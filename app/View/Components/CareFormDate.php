<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CareFormDate extends Component
{

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.care-portal-form-date');
    }

    public function getParsedStart()
    {

        $startDate = new \DateTime('now + 2 days');

        return parseDate($startDate);
    }

    public function getMonthLabelByNumber($num)
    {
        $dateObj = \DateTime::createFromFormat('!m', $num);
        return strftime('%b', $dateObj->getTimestamp());
    }
}
