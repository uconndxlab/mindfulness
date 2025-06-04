<?php

namespace App\Http\Controllers;

use App\Models\ScormPackage;
use App\Models\ScormSession;
use Illuminate\Http\Request;

class ScormController extends Controller
{
    public function initialize(Request $request, ScormPackage $package)
    {
        $session = ScormSession::firstOrCreate([
            'user_id' => auth()->id(),
            'scorm_package_id' => $package->id,
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'cmi_data' => $session->cmi_data ?? []
        ]);
    }

    public function setValue(Request $request, ScormSession $session)
    {
        $this->authorize('update', $session); // Add appropriate policy

        $key = $request->input('key');
        $value = $request->input('value');
        
        $cmiData = $session->cmi_data ?? [];
        $cmiData[$key] = $value;
        
        // Handle special cases
        if ($key === 'cmi.core.lesson_status') {
            $session->lesson_status = $value;
        } elseif ($key === 'cmi.core.score.raw') {
            $session->score = $value;
        }
        
        $session->cmi_data = $cmiData;
        $session->save();

        return response()->json(['success' => true]);
    }

    public function getValue(Request $request, ScormSession $session)
    {
        $this->authorize('view', $session);
        
        $key = $request->input('key');
        $cmiData = $session->cmi_data ?? [];
        
        return response()->json([
            'value' => $cmiData[$key] ?? ''
        ]);
    }

    public function commit(Request $request, ScormSession $session)
    {
        $this->authorize('update', $session);
        
        // Save any pending changes
        $session->save();
        
        return response()->json(['success' => true]);
    }

    public function terminate(Request $request, ScormSession $session)
    {
        $this->authorize('update', $session);
        
        // Perform any cleanup or final calculations
        $session->save();
        
        return response()->json(['success' => true]);
    }
}
