<?php

namespace App\Services;

use App\Events\DayCompleted;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Module;
use App\Models\User;

class ProgressService
{
    // PROCESS
    // 1. complete activity DONE
    //  - mark activity as completed DONE
    //  - unlock optional activities DONE
    //  - unlock next activity within day DONE
    //  - OR check for day completion DONE
    // 2. check day completion
    //  - check for completion of all required activities DONE
    //  - mark day as completed DONE
    //  - unlock next day within module DONE
    //      - unlock first activity of next day DONE
    //  - OR check for module completion DONE
    // 3. check module completion
    //  - check for completion of all days DONE
    //  - mark module as completed DONE
    //  - unlock next module DONE
    //      - unlock first day of next module DONE
    //          - unlock first activity of next module DONE
    // - OR check for course completion DONE

    public function completeActivity(?User $user, ?Activity $activity)
    {
        // init result array
        $result = [
            'activity_completed' => false,
            'optional_unlocked' => false,
            'error' => null
        ];

        // check user
        if (!$user || !$activity) {
            $result['error'] = 'User or activity not found';
            return $result;
        }

        // complete activity
        $user->activities()->syncWithoutDetaching([
            $activity->id => [
                'completed' => true,
                'completed_at' => now(),
            ],
        ]);
        $result['activity_completed'] = true;
        // activity completion logged in activity controller

        // if activity is not optional
        if (!$activity->optional) {
            // find next activity in day
            $nextAct = $activity->day->activities()
                ->where('order', '>', $activity->order)
                ->where('optional', false)
                ->orderBy('order')
                ->first();
    
            // it does not matter if this activity is completed or not
            // completion of current day will still check for day completion
            // no further day unlock necessary
            // means skip check is arbitrary
    
            // if next act is completed, check day completion
            if ($nextAct && !$nextAct->isCompletedBy($user)) {
                // unlock next activity within day
                $actResult = $this->unlockActivity($user, $nextAct);
                $result = array_merge($result, $actResult);
            } else {
                // check day completion
                $dayResult = $this->checkDayCompletion($user, $activity->day);
                $result = array_merge($result, $dayResult);
            }
        }
        return $result;
    }

    public function unlockActivity(User $user, Activity $activity)
    {
        $result = [
            'next_activity_unlocked' => false,
            'next_activity' => null,
        ];

        $user->activities()->syncWithoutDetaching([
            $activity->id => [
                'unlocked' => true,
            ],
        ]);
        $result['next_activity_unlocked'] = true;
        $result['next_activity'] = $activity;

        return $result;
    }

    public function checkDayCompletion(User $user, Day $day) {
        // init result array
        $result = [];

        // get required activities and check for completion
        $requiredActs = $day->activities()->where('optional', false)->get();
        $completed = true;
        foreach ($requiredActs as $act) {
            if (!$user->isActivityCompleted($act)) {
                $completed = false;
                break;
            }
        }

        if ($completed) {
            $completeResult = $this->completeDay($user, $day);
            $result = array_merge($result, $completeResult);
        }
        return $result;
    }

    public function completeDay(User $user, Day $day) {
        // init result array
        $result = [
            'day_completed' => false
        ];

        // complete day
        $user->days()->syncWithoutDetaching([
            $day->id => [
                'completed' => true,
                'completed_at' => now(),
            ],
        ]);
        $result['day_completed'] = true;
        // fire day completion event
        event(new DayCompleted($day->id));
        // log day completion
        activity('day')
            ->event('day_completed')
            ->performedOn($day)
            ->causedBy($user)
            ->withProperties([
                'day' => $day->name,
                'module' => $day->module->name,
            ])
            ->log('Day completed');
        
        // unlock optional
        $optional = $day->activities()
            ->where('optional', true)
            ->get();
        if ($optional->count() > 0) {
            $result['optional_unlocked'] = true;
            foreach ($optional as $opt) {
                $user->activities()->syncWithoutDetaching([
                    $opt->id => [
                        'unlocked' => true,
                    ],
                ]);
            }
        }

        // completion warning for quick completion
        // ignore check in days
        if (!$day->is_check_in) {
            $user->quick_progress_warning = true;
            $user->last_day_completed_id = $day->id;
            $user->save();
        }

        // get next day within module
        $nextDay = $day->module->days()
            ->where('order', '>', $day->order)
            ->orderBy('order')
            ->first();
        
        if ($nextDay) {
            // unlock day
            $dayResult = $this->unlockDay($user, $nextDay);
            $result = array_merge($result, $dayResult);
        } else {
            // check module completion
            $moduleResult = $this->checkModuleCompletion($user, $day->module);
            $result = array_merge($result, $moduleResult);
        }
        return $result;
    }

    public function unlockDay(User $user, Day $day) {
        $result = [
            'next_day_unlocked' => false,
            'next_day' => null,
        ];

        // unlock day
        $user->days()->syncWithoutDetaching([
            $day->id => [
                'unlocked' => true,
            ],
        ]);
        $result['next_day_unlocked'] = true;
        $result['next_day'] = $day;

        // unlock first activity of next day
        $nextAct = $day->activities()->where('optional', false)->orderBy('order')->first();
        if ($nextAct) {
            $actResult = $this->unlockActivity($user, $nextAct);
            $result = array_merge($result, $actResult);
        }
        return $result;
    }

    public function checkModuleCompletion(User $user, Module $module) {
        // init result array
        $result = [];

        // get required days and check for completion
        $requiredDays = $module->days;
        $completed = true;
        foreach ($requiredDays as $day) {
            if (!$user->isDayCompleted($day)) {
                $completed = false;
                break;
            }
        }

        if ($completed) {
            $completeResult = $this->completeModule($user, $module);
            $result = array_merge($result, $completeResult);
        }
        return $result;
    }

    public function completeModule(User $user, Module $module) {
        // init result array
        $result = [
            'module_completed' => false,
            'course_completed' => false
        ];

        // complete module
        $user->modules()->syncWithoutDetaching([
            $module->id => [
                'completed' => true,
                'completed_at' => now(),
            ],
        ]);
        $result['module_completed'] = true;
        // log module completion
        activity('module')
            ->event('module_completed')
            ->performedOn($module)
            ->causedBy($user)
            ->withProperties([
                'module' => $module->name,
            ])
            ->log('Module completed');

        // get next module
        $nextModule = Module::where('order', '>', $module->order)->orderBy('order')->first();
        if ($nextModule) {
            // unlock module
            $moduleResult = $this->unlockModule($user, $nextModule);
            $result = array_merge($result, $moduleResult);
        }
        else {
            // check course completion
            $completed = true;
            $requiredModules = Module::all();
            foreach ($requiredModules as $mod) {
                if (!$user->isModuleCompleted($mod)) {
                    $completed = false;
                    break;
                }
            }
            if ($completed) {
                $result['course_completed'] = true;
            }
        }
        return $result;
    }

    public function unlockModule(User $user, Module $module) {
        $result = [
            'next_module_unlocked' => false,
            'next_module' => null,
        ];

        // unlock module
        $user->modules()->syncWithoutDetaching([
            $module->id => [
                'unlocked' => true,
            ],
        ]);
        $result['next_module_unlocked'] = true;
        $result['next_module'] = $module;

        // unlock first day of next module (will unlock first activity of next day)
        $nextDay = $module->days()->orderBy('order')->first();
        if ($nextDay) {
            $dayResult = $this->unlockDay($user, $nextDay);
            $result = array_merge($result, $dayResult);
        }
        return $result;
    }


    // PROGRESS EVALUATION - functions to run after content management actions
    /**
     * Unlock activities up to latest completion for all users
     * Call this after content management actions that affect activity order
     */
    public function unlockAllUsersUpToLatestCompletion(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $this->unlockUpToLatestCompletion($user);
        }
    }

    /**
     * Unlock activities up to latest completion for a single user
     * This ensures all activities up to their latest completion are unlocked,
     * plus the next activity after their highest completion
     */
    public function unlockUpToLatestCompletion(User $user): void
    {
        $latestCompleted = $this->getLatestCompletedActivity($user);

        if ($latestCompleted) {
            // Unlock all activities up to and including the latest completed
            $this->unlockActivitiesUpTo($user, $latestCompleted);
            
            // Unlock the next activity after the latest completed
            $this->unlockNextActivity($user, $latestCompleted);

            // check day and module completion up to latest completion
            $this->checkDayCompletionUpTo($user, $latestCompleted->day);
            $this->checkModuleCompletionUpTo($user, $latestCompleted->day->module);
        }
    }

    /*
    * Get the latest completed activity for a user
    */
    public function getLatestCompletedActivity(User $user): ?Activity
    {
        return $user->activities()
            ->wherePivot('completed', true)
            ->where('optional', false)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Unlock all activities up to and including the specified activity
     */
    public function unlockActivitiesUpTo(User $user, Activity $activity): void
    {
        $activitiesToUnlock = Activity::where('order', '<=', $activity->order)
            ->orderBy('order')
            ->get();

        foreach ($activitiesToUnlock as $act) {
            $user->activities()->syncWithoutDetaching([
                $act->id => [
                    'unlocked' => true,
                ],
            ]);

            // unlock day and module if they are not unlocked
            // should only run once for each day and module if not already unlocked
            // handles case where there is a new day or module
            if (!$act->day->canBeAccessedBy($user)) {
                $user->days()->syncWithoutDetaching([
                    $act->day_id => [
                        'unlocked' => true,
                    ],
                ]);
                if (!$act->day->module->canBeAccessedBy($user)) {
                    $user->modules()->syncWithoutDetaching([
                        $act->day->module_id => [
                            'unlocked' => true,
                        ],
                    ]);
                }
            }
        }
    }

    /**
     * Unlock the next activity after the specified activity
     */
    public function unlockNextActivity(User $user, Activity $activity): ?Activity
    {
        $nextActivity = Activity::where('order', '>', $activity->order)
            ->orderBy('order')
            ->where('optional', false)
            ->first();

        if ($nextActivity) {
            $user->activities()->syncWithoutDetaching([
                $nextActivity->id => [
                    'unlocked' => true,
                ],
            ]);
            $user->days()->syncWithoutDetaching([
                $nextActivity->day_id => [
                    'unlocked' => true,
                ],
            ]);
            $user->modules()->syncWithoutDetaching([
                $nextActivity->day->module_id => [
                    'unlocked' => true,
                ],
            ]);
            return $nextActivity;
        }

        return null;
    }

    /**
     * Check day completion up to latest completion - if completed, complete day in evaluation
     */
    public function checkDayCompletionUpTo(User $user, Day $day): void
    {
        $days = Day::where('order', '<=', $day->order)
            ->orderBy('order')
            ->get();

        foreach ($days as $day) {
            if ($user->isDayCompleted($day)) {
                continue;
            }

            $completed = true;
            foreach ($day->activities()->where('optional', false)->get() as $activity) {
                if (!$user->isActivityCompleted($activity)) {
                    $completed = false;
                    break;
                }
            }
            if ($completed) {
                $this->completeDayInEvaluation($user, $day);
            }
        }
    }

    // complete day in evaluation - has different behavior than normal completion - just completion and event
    public function completeDayInEvaluation(User $user, Day $day): void
    {
        $user->days()->syncWithoutDetaching([
            $day->id => [
                'completed' => true,
                'completed_at' => now(),
            ],
        ]);
        activity('day')
            ->event('day_completed_in_evaluation')
            ->performedOn($day)
            ->causedBy($user)
            ->withProperties([
                'day' => $day->name,
                'module' => $day->module->name,
            ])
            ->log('Day completed in App Update');
    }

    /**
     * Check module completion up to latest completion - if completed, complete module in evaluation
     */
    public function checkModuleCompletionUpTo(User $user, Module $module): void
    {
        $modules = Module::where('order', '<=', $module->order)
            ->orderBy('order')
            ->get();

        foreach ($modules as $module) {
            if ($user->isModuleCompleted($module)) {
                continue;
            }

            $completed = true;
            foreach ($module->days()->get() as $day) {
                if (!$user->isDayCompleted($day)) {
                    $completed = false;
                    break;
                }
            }
            if ($completed) {
                $this->completemoduleInEvaluation($user, $module);
            }
        }
    }

    public function completeModuleInEvaluation(User $user, Module $module): void
    {
        $user->modules()->syncWithoutDetaching([
            $module->id => [
                'completed' => true,
                'completed_at' => now(),
            ],
        ]);
        activity('module')
            ->event('module_completed_in_evaluation')
            ->performedOn($module)
            ->causedBy($user)
            ->withProperties([
                'module' => $module->name,
            ])
            ->log('Module completed in App Update');
    }
}
