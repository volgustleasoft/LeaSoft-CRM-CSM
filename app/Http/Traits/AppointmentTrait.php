<?php

namespace App\Http\Traits;

use App\Models\Appointment;
use App\Models\CareGroup;
use App\Models\Person;
use App\Models\QuestionCategory;
use Exception;

trait AppointmentTrait
{

    use AgendaTrait;

    /**
     * @param Appointment $appointment
     * @param bool $isCaregiver
     * @return array
     * @throws Exception
     */
    public function prepare_appointment_data(Appointment $appointment, $isCaregiver = false)
    {

        $question = $appointment->getQuestion;
        $from = new \DateTime($appointment->DateTimeAppointmentFrom);
        $to = new \DateTime($appointment->DateTimeAppointmentTo);
        $cg = Person::find($appointment->AcceptedByPersonId);

        $visitors = [];

        return [
            "type" => $question->AppointmentType,
            "careGiver" => [
                'id' => $cg->Id,
                "firstname" => $cg->Firstname,
                "lastname" => $cg->Lastname,
                "VideoCallURLForCG" => $appointment->VideoCallUrlCareGiver,
                "IsCareCaregiver" => $isCaregiver,
                "careGiverTeam" => CareGroup::find($cg->TeamCareGroupId)->Name,
            ],
            "report" => $question->Report,
            "cancelReason" => $appointment->CancelReason,
            "datetimeFrom" => strftime($from->format('r')),
            "datetimeTo" => strftime($to->format('r')),
            "shouldReport" => $appointment->shouldReport($this->person),
            "id" => $appointment->Id,
            "state" => ($to->getTimeStamp() < time()) ? "completed" : $appointment->State,
            "date" => $from->format("Y-m-d"),
            "question" => [
                "state" => ($to->getTimeStamp() < time()) ? "Afgerond" : $this->appointmentStateText($appointment),
                "id" => $question->Id,
                "remarks" => $question->Report,
                "category" => !empty(QuestionCategory::where('Id', $question->QuestionCategory->ParentId)->first()) ? QuestionCategory::where('Id', $question->QuestionCategory->ParentId)->first()->Name . " > " . $question->QuestionCategory->Name : $question->QuestionCategory->Name,
                "type" => $question->AppointmentType,
                "question" => $question->Description,
                "client" => [
                    "firstname" => $question->Person->Firstname,
                    "lastname" => $question->Person->Lastname,
                    "phone" => $question->Person->PhoneNumber,
                    "email" => $question->Person->Mail,
                    "team" => $question->Person->ClientTeam->Name,
                    "address" => [
                        "street" => $question->Person->AddressStreet,
                        "number" => $question->Person->AddressNumber,
                        "zipcode" => $question->Person->AddressZipcode,
                        "city" => $question->Person->AddressCity
                    ]
                ],
            ],
        ];
    }

    /**
     * Function appointmentStateText
     *
     * @param Appointment $appointment
     * @return string
     */
    public function appointmentStateText(Appointment $appointment): string
    {
        if ($appointment->shouldReport($this->person)) {
            return __("states.appointment_completed_and_should_report");
        } else {
            return __("states.appointment_$appointment->State");
        }
    }
}
