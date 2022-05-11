<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'Question';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['PersonId', 'QuestionCategoryId', 'Description', 'AppointmentType', 'ExternalLocationAllowed', 'State', 'NotifiedGroups'];

    public function Appointments()
    {
        return $this->hasMany(Appointment::class, "QuestionId", "Id");
    }

    public function getAppointment()
    {
        return $this->hasOne(Appointment::class, "QuestionId", "Id");
    }

    public function QuestionCategory()
    {
        return $this->hasOne(QuestionCategory::class, "Id", "QuestionCategoryId");
    }

    public function CareGroupLabels()
    {
        return $this->hasMany(QuestionCareGroupLabel::class, "QuestionId", "Id");
    }

    public function Person()
    {
        return $this->hasOne(Person::class, "Id", "PersonId");
    }

    public function QuestionAvailableTimeSlot()
    {
        return $this->hasOne(QuestionAvailableTimeSlot::class, "QuestionId", "Id");
    }

    public function CreateQuestionAvailableTimeSlot($StartDateTime, $EndDateTime) {
        QuestionAvailableTimeSlot::create([
              "QuestionId" => $this->Id,
              "DateTimeFrom" => getUTC($StartDateTime)->format("Y-m-d H:i:s"),
              "DateTimeTo" => getUTC($EndDateTime)->format("Y-m-d H:i:s"),
          ]);
    }

    public function CreateQuestionApointment() {
        Appointment::create([
            "QuestionId" => $this->Id
        ]);
    }

    public function CreateQuestionCareGroupLabel() {
        QuestionCareGroupLabel::create([
           "QuestionId" => $this->Id,
           "CareGroupLabelId" => CareGroupLabel::where('PersonType', "client")->first()->Id,
           "IsSelected" => 0,
       ]);
    }

    public function getFullCategory() {
        $category = $this->QuestionCategory;
        $categories = [ $category->Name ];

        if(! empty($category->ParentId)) {
            while($row = QuestionCategory::find($category->ParentId)) {
                $category = $row;
                $categories[] = $category->Name;
            }
        }
        return array_reverse($categories);
    }

   public function GetRolesFromQuestion(){

        $selectedRoles = array();
        foreach($this->CareGroupLabels->where('IsSelected', true) as $roleLabel){
            switch ($roleLabel->CareGroupLabelId) {
                case "1":
                    array_push($selectedRoles, "Caregiver");
                    break;
                case "2":
                    array_push($selectedRoles,  "Personal Caregiver");
                    break;
            }
        }
        return implode(", ", $selectedRoles);
    }

    public function setAvailableTimeSlot($timeslot, $duration = 15){
        QuestionAvailableTimeSlot::query()
            ->where('QuestionID', '=', $this->Id)
            ->delete();

        return QuestionAvailableTimeSlot::create([
              "QuestionId" => $this->Id,
              "DateTimeFrom" => $timeslot->format("Y-m-d H:i:s"),
              "DateTimeTo" => date("Y-m-d H:i:s", $timeslot->getTimestamp()+$duration*60),
        ]);
    }

    public function getCurrentAppointment($state = '', $PersonId = false){
        $query = Appointment::query()
            ->select('Appointment.*')
            ->join('Question', 'Appointment.QuestionId', '=', 'Question.ID');

        if ($state) {
            $query->when(is_array($state), function ($query) use ($state){
                $query->whereIn('Appointment.State', $state);
            }, function ($query) use ($state) {
                $query->where('Appointment.State', '=', $state);
            });
        }

        if ($PersonId) {
            $query->where('Appointment.AcceptedByPersonId', '=', $PersonId);
            $query->orWhere('Question.PersonId', '=', $PersonId);
        }
        $query->orderBy('Appointment.Id', 'desc');

        return $query->get()->first();
    }

    public function QuestionCareGroupsLabels()
    {
        return $this->belongsToMany(CareGroupLabel::class, 'QuestionCareGroupLabel', 'QuestionId', 'CareGroupLabelId')
            ->where('IsSelected', 1);
    }
}
