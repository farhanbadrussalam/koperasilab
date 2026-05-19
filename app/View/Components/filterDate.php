<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class filterDate extends Component
{
    public string $styleType, $default;

    /**
     * Create a new component instance.
     */
    public function __construct($styleType = 'full', $default = "")
    {
        $this->styleType = $styleType;
        switch ($default) {
            case 'monthly':
                $default = 'Bulan Ini';
                break;
            case 'yearly':
                $default = 'Tahun Ini';
                break;
            case 'today':
                $default = 'Hari Ini';
                break;
            case 'weekly':
                $default = 'Minggu Ini';
                break;
            default:
                $default = $default;
                break;
        }

        $this->default = $default;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filter-date');
    }
}
