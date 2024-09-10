<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SearchResults extends Component
{
    public $activities;
    /**
     * Create a new component instance.
     */
    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.search-results');
    }
}
