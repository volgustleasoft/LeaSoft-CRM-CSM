<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TimeCrossing implements Rule
{
    private $timeStart = 0;
    private $timeEnd = 0;
    private $validationPerson;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($timeStart, $timeEnd, $person)
    {
        $this->timeStart = getUTC($timeStart)->getTimestamp();
        $this->timeEnd = getUTC($timeEnd)->getTimestamp();
        $this->validationPerson = $person;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You cannot schedule a new registration consultation hour for this time, because one is already planned in your name.';    }
}
