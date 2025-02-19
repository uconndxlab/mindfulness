<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class audioPlayer extends Component
{
    
    public $file;
    public $id;
    public $controlsList;
    public $allowSeek;

    /**
     * Create a new component instance.
     */
    public function __construct($file, $id = null, $controlsList = null, $allowSeek = false)
    {
        $this->file = $file;
        $this->id = $id;
        $this->controlsList = $controlsList;    
        $this->allowSeek = $allowSeek;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.audio-player');
    }
}
