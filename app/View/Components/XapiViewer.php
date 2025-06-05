<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\XapiPackage;
use App\Http\Controllers\XapiPackageController;

class XapiViewer extends Component
{
    public $iframeSrc;
    
    public function __construct(XapiPackageController $packageController, $packageId) {
        $package = XapiPackage::findOrFail(intval($packageId));
        $this->iframeSrc = $packageController->getIframeSrc($package);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.xapi-viewer');
    }
}
