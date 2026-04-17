<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VoiceSelector extends Component
{
    public $voices;
    public $defaultVoice;
    public $showDropdown;
    public $multipleVoices;

    /**
     * Create a new component instance.
     */
    public function __construct(array $voices = [], string $defaultVoice = '', bool $showDropdown = false, $multipleVoices = false)
    {
        $this->voices = $voices;
        $this->defaultVoice = $defaultVoice;
        $this->showDropdown = $showDropdown;
        $this->multipleVoices = $multipleVoices;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.voice-selector');
    }
}
