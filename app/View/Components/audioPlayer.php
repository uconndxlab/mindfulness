<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class audioPlayer extends Component
{
    public $file;
    public $id;
    public $allowSeek;
    public $allowPlaybackRate;
    public $title;
    public $artist;
    public $artwork;
    public $voiceDisplay;

    /**
     * Create a new component instance.
     */
    public function __construct($file, $id = null, $allowSeek = false, $allowPlaybackRate = false, $title = null, $artist = null, $artwork = null, $voiceDisplay = null)
    {
        $this->file = $file;
        $this->id = $id;
        $this->allowSeek = $allowSeek;
        $this->allowPlaybackRate = $allowPlaybackRate;
        $this->title = $title;
        $this->artist = $artist;
        $this->artwork = $artwork;
        $this->voiceDisplay = $voiceDisplay;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.audio-player');
    }
}
