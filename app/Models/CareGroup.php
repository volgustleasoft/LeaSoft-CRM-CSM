<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareGroup extends Model
{
    use HasFactory;

    protected $table = 'CareGroup';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, "Id", "OrganisationId");
    }

    public function CareGroupPersons()
    {
        return $this->hasMany(CareGroupPerson::class, "CareGroupId", "Id");
    }

    public function Persons()
    {
        return $this->belongsToMany(Person::class, 'CareGroupPerson', 'CareGroupId', 'PersonId');
    }

    public function getOpenQuestion($role, $C2CCaregiver = FALSE)
    {
        switch ($role) {
            case "caregiver":
                $labelId = 1;
                $fieldName = "TeamCareGroupId";
                break;
            case "personalcaregiver":
                $labelId = 2;
                $fieldName = "PersonalCareGroupId";
                break;
            default:
                break;
        }

        $query = Question::query()
            ->select(
                'Question.*',
                'QuestionAvailableTimeSlot.DateTimeFrom',
                'QuestionAvailableTimeSlot.DateTimeTo')
            ->distinct()
            ->join('Person', 'Person.Id', '=', 'Question.PersonId')
            ->join('Appointment', 'Appointment.QuestionId', '=', 'Question.Id')
            ->join('QuestionCareGroupLabel', 'QuestionCareGroupLabel.QuestionId', '=',  'Question.Id')
            ->join('QuestionAvailableTimeSlot', 'QuestionAvailableTimeSlot.QuestionId','=','Question.Id')
            ->where('QuestionCareGroupLabel.IsSelected', '=', 1)
            ->whereNull('Appointment.AcceptedByPersonId')
            ->whereIn('QuestionCareGroupLabel.CareGroupLabelId',is_array($labelId) ? $labelId : [$labelId])
            ->whereNull('Appointment.AcceptedByPersonId')
            ->where('Person.'.$fieldName, '=', $this->Id)
            ->where('Question.State',  '=', 'InProgress')->orWhere('Question.State', '=', 'new')
            ->where('Appointment.State', '=', 'new');

        return $query->orderBy('QuestionAvailableTimeSlot.DateTimeFrom', 'ASC')->get()->keyBy('Id');
    }

    public function Clients()
    {
        return $this->hasMany(Person::class, "TeamCareGroupId", "Id")->orderBy('Lastname', 'asc');
    }

    public function CaregiversFromPersonTeam()
    {
        return Person::query()
            ->select('Person.*')
            ->join('CareGroupPerson', 'Person.Id', '=', 'CareGroupPerson.PersonId')
            ->join('CareGroup', 'CareGroup.Id', '=', 'CareGroupPerson.CareGroupId')
            ->where('CareGroupPerson.CareGroupId', $this->Id)
            ->get();
    }

    public function getWorkHours($dayNumber, $appointmentType)
    {
        $query = WorkHours::query()
            ->where('DayNumber', '=', $dayNumber)
            ->where('AppointmentType', '=', $appointmentType)
            ->where('TeamCareGroupId', '=', $this->Id);
        return $query->get()->first();
    }

    public function getDefaultWorkHours($dayNumber, $appointmentType) {
        $query = WorkHours::query()
              ->where('DayNumber', '=', $dayNumber)
              ->where('AppointmentType', '=', $appointmentType)
              ->where('OrganisationId', '=', $this->OrganisationId);

        return $query->get()->first();
    }

    public function hasWorkingHours($dayNumber, $appointmentType)
    {
        $result = WorkHours::query()
                           ->where('TeamCareGroupId', '=', $this->Id)
                           ->where('DayNumber', '=', $dayNumber)
                           ->where('AppointmentType', '=', $appointmentType)
                           ->get()->all();

        return empty($result) ? false : true;
    }

    public function createWorkHours($hours, $dayNumber, $appointmentType)
    {
        return WorkHours::create([
              "Hours" => json_encode($hours),
              "DayNumber" => $dayNumber,
              "AppointmentType" => $appointmentType,
              "TeamCareGroupId" => $this->Id
          ]);
    }

    public function updateWorkHours($hours, $dayNumber, $appointmentType)
    {
        return WorkHours::query()
            ->where('TeamCareGroupId', '=', $this->Id)
            ->where('DayNumber', '=', $dayNumber)
            ->where('AppointmentType', '=', $appointmentType)
            ->update([ "Hours" => json_encode($hours)]);
    }

    public function removeWorkingHours($dayNumber, $appointmentType)
    {
        return WorkHours::query()
             ->where('TeamCareGroupId', '=', $this->Id)
             ->where('DayNumber', '=', $dayNumber)
             ->where('AppointmentType', '=', $appointmentType)
             ->delete();
    }

    public function getWorkHoursException($appointmentType, $minDate = null, $maxDate = null)
    {
        $operator = ((! empty($minDate) and ! empty($maxDate)) or (empty($minDate) and empty($maxDate))) ? '>=' : '=';
        $minDate = empty($minDate) ? date("Y-m-d") : $minDate;
        $query = WorkHoursException::query()
            ->where('TeamCareGroupId', '=', $this->Id)
            ->where('ExceptionDate', $operator, $minDate)
            ->where('AppointmentType', '=', $appointmentType);

        if ($maxDate != null) {
            $query->where('ExceptionDate', '<=', $maxDate);
        }
       return $query->orderBy('ExceptionDate', 'asc')->get();
    }

    public function addExceptionWorkHours($hours, $date, $appointmentType)
    {
        return WorkHoursException::updateOrCreate(['ExceptionDate' => $date, 'AppointmentType' => $appointmentType],[
             "Hours" => json_encode($hours),
             "TeamCareGroupId" => $this->Id
         ]);
    }

    public function removeExceptionWorkHours($date, $appointmentType)
    {
        return WorkHoursException::query()
          ->where('TeamCareGroupId', '=', $this->Id)
          ->where('ExceptionDate', '=', $date)
          ->where('AppointmentType', '=', $appointmentType)
          ->delete();
    }
}

