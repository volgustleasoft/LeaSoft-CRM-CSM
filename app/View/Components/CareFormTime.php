<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CareFormTime extends Component
{

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.care-portal-form-time');
    }

    public function getParsedStart()
    {
        $startDate = new \DateTime('now + 2 days');

        return parseDate($startDate);
    }

    public function getParsedEnd()
    {
        $endDate = new \DateTime('now + 2 days 30 minutes');

        return parseDate($endDate);
    }

    public function isAvailable()
    {
        return empty($this->inloop) ? true : false;
    }
}
