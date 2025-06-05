<?php

namespace App\Http\Controllers;

use App\Models\XapiPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class XapiStatementController extends Controller
{
    public function store(Request $request)
    {
        // get statements from request
        $statements = $request->json()->all();

        // handle single statement or array of statements
        if (isset($statements['verb'])) {
            $statements = [$statements];
        }

        foreach ($statements as $index => $statement) {
            // validate statement
            $validator = Validator::make($statement, [
                'actor.mbox' => 'required_without:actor.account|string',
                'actor.account' => 'required_without:actor.mbox|array',
                'verb.id' => 'required|url',
                'object.id' => 'required|url',
            ]);
            if ($validator->fails()) {
                continue;
            }

            // get verb id and activity id from statement
            $verbId = $statement['verb']['id'];
            $activityIdFromStatement = $statement['object']['id'];

            $completionVerbs = [
                'http://adlnet.gov/expapi/verbs/completed',
                'http://adlnet.gov/expapi/verbs/passed',
            ];

            // check for completion verb to update completion
            if (in_array($verbId, $completionVerbs)) {
                $xapiPackage = XapiPackage::where('xapi_activity_id', $activityIdFromStatement)->first();
                
                if ($xapiPackage) {
                    // store completion status in cache
                    $cacheKey = 'xapi_completion_'.$xapiPackage->id.'_'.auth()->id();
                    Cache::put($cacheKey, true, now()->addMinutes(30));
                    \Log::info('XapiStatementController: completion stored in cache', [
                        'package_id' => $xapiPackage->id,
                        'user_id' => auth()->id()
                    ]);
                }
            }
        }

        return response()->noContent();
    }

    public function checkCompletion($packageId)
    {
        $cacheKey = 'xapi_completion_'.$packageId.'_'.auth()->id();
        $isCompleted = Cache::get($cacheKey, false);
        
        return response()->json(['completed' => $isCompleted]);
    }
}
