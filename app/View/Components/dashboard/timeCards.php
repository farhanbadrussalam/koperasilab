<?php

namespace App\View\Components\dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Carbon;

class timeCards extends Component
{
    /**
     * Create a new component instance.
     */
    public $timenow;
    public function __construct()
    {
        $this->timenow = convert_date(Carbon::now(), 4);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard.time-cards');
    }
}
