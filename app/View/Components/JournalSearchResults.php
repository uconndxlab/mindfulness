<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class JournalSearchResults extends Component
{
    public $notes;
    /**
     * Create a new component instance.
     */
    public function __construct($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.journal-search-results');
    }
}
