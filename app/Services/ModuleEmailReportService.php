<?php

namespace App\Services;

use App\Models\Module;
use App\Models\QuizAnswers;
use App\Models\User;

class ModuleEmailReportService
{
    private const BEGINNING_DAY_ID = 1;

    public function forUserAndPart(User $user, int $completedPartOrder): array
    {
        $module = Module::where('order', $completedPartOrder)->firstOrFail();

        return [
            'part' => $completedPartOrder,
            'module' => $module,
            'emotions' => [
                'chart_title' => "Rate My Emotions Measure",
                'sections' => $this->emotionSections($user, $completedPartOrder),
            ],
            'presence' => [
                'chart_title' => "Rate My Presence Measure",
                'sections' => $this->presenceSections($user, $completedPartOrder),
            ],
            'awareness_quality' => [
                'chart_title' => 'Daily Check-Ins & Final Awareness Score',
                'check_ins' => $this->dailyCheckInSeries($user, $completedPartOrder),
                'awareness' => $this->endOfPartAwareness($user, $completedPartOrder),
            ],
        ];
    }

    public function scaleToLikert(?float $percent): ?float
    {
        if ($percent === null) {
            return null;
        }

        return round(($percent / 100) * 4 + 1, 1);
    }

    private function emotionSections(User $user, int $completedPartOrder): array
    {
        $sections = [];

        $beginning = $this->emotionScoresFromAnswer(
            $this->selfRatingAnswer($user, 'Rate My Emotions', beginning: true)
        );
        if ($beginning !== null) {
            $sections[] = array_merge(['label' => 'Beginning of Part 1'], $beginning);
        }

        for ($partOrder = 1; $partOrder <= $completedPartOrder; $partOrder++) {
            $end = $this->emotionScoresFromAnswer(
                $this->selfRatingAnswer($user, 'Rate My Emotions', beginning: false, partOrder: $partOrder)
            );
            if ($end !== null) {
                $sections[] = array_merge(['label' => "End of Part {$partOrder}"], $end);
            }
        }

        return $sections;
    }

    private function presenceSections(User $user, int $completedPartOrder): array
    {
        $sections = [];

        $beginningAverage = $this->beginningSelfRatingAverage($user, 'Rate My Presence in Parenting');
        if ($beginningAverage !== null) {
            $sections[] = [
                'label' => 'Beginning of Part 1',
                'value' => $this->scaleToLikert($beginningAverage),
            ];
        }

        for ($partOrder = 1; $partOrder <= $completedPartOrder; $partOrder++) {
            $endAverage = $this->endOfPartSelfRatingAverage($user, 'Rate My Presence in Parenting', $partOrder);
            if ($endAverage !== null) {
                $sections[] = [
                    'label' => "End of Part {$partOrder}",
                    'value' => $this->scaleToLikert($endAverage),
                ];
            }
        }

        return $sections;
    }

    private function dailyCheckInSeries(User $user, int $partOrder): array
    {
        $module = Module::where('order', $partOrder)->first();
        if (!$module) {
            return [];
        }

        $points = [];
        $days = $module->days()->where('is_check_in', false)->orderBy('order')->get();

        foreach ($days as $index => $day) {
            $average = $this->averageForQuery(
                $user->quiz_answers()
                    ->checkIns()
                    ->whereHas('activity.day', fn ($query) => $query->where('id', $day->id))
            );

            if ($average !== null) {
                $points[] = [
                    'label' => (string) ($index + 1),
                    'value' => $this->scaleToLikert($average),
                ];
            }
        }

        return $points;
    }

    private function endOfPartAwareness(User $user, int $partOrder): ?array
    {
        $average = $this->endOfPartSelfRatingAverage($user, 'Rate My Awareness', $partOrder);
        if ($average === null) {
            return null;
        }

        return [
            'label' => "End of Part {$partOrder}",
            'value' => $this->scaleToLikert($average),
        ];
    }

    private function selfRatingAnswer(
        User $user,
        string $activityTitle,
        bool $beginning,
        ?int $partOrder = null
    ): ?QuizAnswers {
        $query = $user->quiz_answers()
            ->selfRatings()
            ->with('quiz')
            ->whereHas('activity', fn ($q) => $q->where('title', $activityTitle));

        if ($beginning) {
            $query->whereHas('activity', fn ($q) => $q->where('day_id', self::BEGINNING_DAY_ID));
        } else {
            $module = Module::where('order', $partOrder)->first();
            if (!$module) {
                return null;
            }

            $query->whereHas('activity.day', fn ($q) => $q
                ->where('module_id', $module->id)
                ->where('is_check_in', true));
        }

        return $query->latest('id')->first();
    }

    private function emotionScoresFromAnswer(?QuizAnswers $answer): ?array
    {
        if (!$answer?->quiz) {
            return null;
        }

        $surveyAnswers = $this->extractSurveyAnswers($answer);
        if ($surveyAnswers === null) {
            return null;
        }

        $options = $answer->quiz->question_options[0]['options'] ?? [];
        $pleasant = [];
        $unpleasant = [];

        foreach ($options as $option) {
            $optionId = (string) ($option['id'] ?? '');
            if (!array_key_exists($optionId, $surveyAnswers)) {
                continue;
            }

            $score = (float) $surveyAnswers[$optionId];
            if ($option['inverse_score'] ?? false) {
                $unpleasant[] = $score;
            } else {
                $pleasant[] = $score;
            }
        }

        if ($pleasant === [] && $unpleasant === []) {
            return null;
        }

        return [
            'pleasant' => $pleasant !== [] ? round(array_sum($pleasant) / count($pleasant), 1) : null,
            'unpleasant' => $unpleasant !== [] ? round(array_sum($unpleasant) / count($unpleasant), 1) : null,
        ];
    }

    private function extractSurveyAnswers(QuizAnswers $answer): ?array
    {
        $answers = $answer->answers;
        if (!is_array($answers) || $answers === []) {
            return null;
        }

        $surveyData = reset($answers);
        if (!is_array($surveyData)) {
            return null;
        }

        $parsed = [];
        foreach ($surveyData as $item) {
            if (!is_array($item)) {
                continue;
            }

            $optionId = (string) array_key_first($item);
            $parsed[$optionId] = $item[$optionId];
        }

        return $parsed !== [] ? $parsed : null;
    }

    private function beginningSelfRatingAverage(User $user, string $activityTitle): ?float
    {
        return $this->averageForQuery(
            $user->quiz_answers()
                ->selfRatings()
                ->whereHas('activity', function ($query) use ($activityTitle) {
                    $query->where('title', $activityTitle)
                        ->where('day_id', self::BEGINNING_DAY_ID);
                })
        );
    }

    private function endOfPartSelfRatingAverage(User $user, string $activityTitle, int $partOrder): ?float
    {
        $module = Module::where('order', $partOrder)->first();
        if (!$module) {
            return null;
        }

        return $this->averageForQuery(
            $user->quiz_answers()
                ->selfRatings()
                ->whereHas('activity', function ($query) use ($activityTitle) {
                    $query->where('title', $activityTitle);
                })
                ->whereHas('activity.day', function ($query) use ($module) {
                    $query->where('module_id', $module->id)
                        ->where('is_check_in', true);
                })
        );
    }

    private function averageForQuery($query): ?float
    {
        $average = $query->avg('average');

        return $average !== null ? (float) $average : null;
    }
}
