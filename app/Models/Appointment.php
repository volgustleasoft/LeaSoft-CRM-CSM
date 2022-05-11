<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'Appointment';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['AcceptedByPersonId', 'QuestionId', 'State', 'DateTimeAppointmentFrom', 'DateTimeAppointmentTo', 'FirstReminderSentDateTime', 'SecondReminderSentDateTime'];

    public function shouldReport($person)
    {
        $Question = Question::find($this->QuestionId);
        return ($this->AcceptedByPersonId == $person->Id)
            && $this->State == 'created'
            && ($this->DateTimeAppointmentTo < Carbon::now()->toDateTimeString() || $Question->IsDirectContact);
    }

    public function getAcceptedByPerson()
    {
        return $this->hasOne(Person::class, "Id", "AcceptedByPersonId");
    }

    public function getQuestion()
    {
        return $this->hasOne(Question::class, "Id", "QuestionId");
    }
}
