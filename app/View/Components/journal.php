<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class journal extends Component
{
    public $journal;
    /**
     * Create a new component instance.
     */
    public function __construct($journal)
    {
        $this->journal = $journal;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.journal');
    }
}
