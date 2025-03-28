<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PdfViewer extends Component
{
    public $fpath;
    public $wbName;
    /**
     * Create a new component instance.
     */
    public function __construct(string $fpath, string $wbName)
    {
        $this->fpath = $fpath;
        $this->wbName = $wbName;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pdf-viewer');
    }
}
