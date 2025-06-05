<?php

namespace App\Http\Controllers;

use App\Models\XapiPackage;
use App\Services\XapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class XapiPackageController extends Controller
{
    protected $xapiService;

    public function __construct(XapiService $xapiService)
    {
        $this->xapiService = $xapiService;
    }

    public function create()
    {
        return view('xapi.upload');
    }

    public function store(Request $request)
    {
        // validate - must be zip
        $request->validate([
            'package_file' => 'required|file|mimes:zip',
            'title' => 'required|string|max:255'
        ]);

        try {
            // process
            $result = $this->xapiService->processPackage($request->file('package_file'));

            $package = XapiPackage::create([
                'title' => $request->input('title'),
                'package_id' => $result['package_id'],
                'entry_point' => $result['entry_point'],
                'xapi_activity_id' => $result['xapi_activity_id']
            ]);

            return back()->with('success', 'Package uploaded successfully!')
            ->with('package', $package);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to process xAPI package: ' . $e->getMessage());
        }
    }
    
    public function show(XapiPackage $package)
    {
        $iframeSrc = $this->getIframeSrc($package);

        return view('xapi.viewer', [
            'package' => $package,
            'iframeSrc' => $iframeSrc,
        ]);
    }

    public function getIframeSrc(XapiPackage $package) {
        $user = auth()->user();
        
        // sanctum token
        $tokenName = 'xapi-launch-'.$package->package_id.'-'.$user->id;
        // prune old tokens
        $user->tokens()->where('name', $tokenName)->delete();
        $token = $user->createToken($tokenName, ['xapi-statements'])->plainTextToken;

        $launchParams = [
            'endpoint' => url('/api/xapi'),
            'auth' => base64_encode('xapi_user:' . $token),
            'actor' => json_encode([
                'mbox' => 'mailto:' . $user->email,
                'name' => $user->name,
                'objectType' => 'Agent'
            ]),
            'registration' => Str::uuid()->toString(),
            'activity_id' => $package->xapi_activity_id
        ];
        
        // build url
        $fullLaunchUrl = Storage::disk('xapi_content')->url('extracted/'.$package->package_id.'/'.ltrim($package->entry_point, '/'));
        // add query params
        $iframeSrc = $fullLaunchUrl.'?'.http_build_query($launchParams);

        return $iframeSrc;
    }

}