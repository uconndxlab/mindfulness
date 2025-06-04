<?php

namespace App\Http\Controllers;

use App\Models\XapiPackage;
use App\Services\XapiService;
use Illuminate\Http\Request;

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
        // TODO
    }
}