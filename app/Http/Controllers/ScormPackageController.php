<?php

namespace App\Http\Controllers;

use App\Models\ScormPackage;
use App\Services\ScormService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScormPackageController extends Controller
{
    protected $scormService;

    public function __construct(ScormService $scormService)
    {
        $this->scormService = $scormService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'package' => 'required|file|mimes:zip',
            'title' => 'required|string|max:255'
        ]);

        try {
            // Process the SCORM package
            $result = $this->scormService->processPackage($request->file('package'));

            // Create the package record
            $package = ScormPackage::create([
                'title' => $request->input('title'),
                'type' => $result['type'],
                'version' => '1.2', // You might want to detect this from the manifest
                'entry_point' => $result['entry_point'],
                'package_id' => $result['package_id'],
                'status' => 'active',
                'xapi_activity_id' => $result['xapi_activity_id'],
            ]);

            return response()->json([
                'success' => true,
                'package' => $package
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process SCORM package: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(ScormPackage $package)
    {
        $entryUrl = null;
        $xapiLaunchParams = null;

        if ($package->type === 'xapi') {
            // Ensure xapi_activity_id and entry_point are available
            if (empty($package->xapi_activity_id) || empty($package->entry_point)) {
                abort(500, 'xAPI package data is incomplete.');
            }

            $actor = json_encode([
                'mbox' => 'mailto:' . auth()->user()->email,
                'name' => auth()->user()->name,
                'objectType' => 'Agent'
            ]);

            // TODO?
            // For simplicity, we'll assume the statement endpoint is on the same domain
            // and will handle authentication based on the user's session or a general API token guard.
            // A more robust solution might involve generating a short-lived JWT here.
            $endpoint = url('/api/xapi/statements'); 

            $launchParams = [
                'endpoint' => $endpoint,
                'auth' => 'Bearer YOUR_AUTH_TOKEN_OR_SESSION_ID_PROXY', // Placeholder - needs proper auth strategy
                'actor' => $actor,
                'registration' => Str::uuid()->toString(),
                'activityId' => $package->xapi_activity_id
            ];

            // Construct the base URL to the xAPI content
            $baseUrl = url("storage/scorm/extracted/{$package->package_id}/{$package->entry_point}");
            
            // Append query parameters
            $entryUrl = $baseUrl . '?' . http_build_query($launchParams);
            $xapiLaunchParams = $launchParams; // Pass for potential JS use, though URL has it

        } else { // SCORM package
            if (empty($package->entry_point)) {
                abort(500, 'SCORM package entry point is missing.');
            }
            $entryUrl = url("storage/scorm/extracted/{$package->package_id}/{$package->entry_point}");
        }
        
        return view('scorm.viewer', [
            'package' => $package,
            'entryUrl' => $entryUrl,
            'packageType' => $package->type, // 'scorm' or 'xapi'
            'xapiLaunchParams' => $xapiLaunchParams // Null if SCORM
        ]);
    }

    public function upload()
    {
        return view('scorm.upload');
    }
}