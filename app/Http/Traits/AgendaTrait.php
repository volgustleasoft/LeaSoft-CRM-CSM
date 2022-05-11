<?php

namespace App\Http\Traits;

use App\Models\Appointment;
use App\Models\ContactLog;
use App\Models\Question;

trait AgendaTrait
{

    /**
     * Caregiver Appontment cancel action helper
     * @param Appointment $appointment
     * @param $reason
     * @return bool
     */
    public function cancelAppointment(Appointment $appointment, $reason)
    {
        $Question = $appointment->getQuestion;
        $appointment->State = "canceledByCaregiver";
        $appointment->CancelReason = $reason;
        $result = $appointment->save();
        $Question->CreateQuestionApointment();
        app('App\Http\Controllers\StatisticsController')->log("question", $this->person, ["SubEvent" => "canceled_by_caregiver", "EventRef" => $Question->Id, "team" => $Question->Person->TeamCareGroupId]);
        return $result;
    }

    /**
     * Report Appointment for Caregiver
     * @param Appointment $Appointment
     * @param $didHappen
     * @param $report
     * @param $failReason
     * @return bool
     */
    public function completeAppointment(Appointment $Appointment, $didHappen, $report, $failReason)
    {
        $slackState = '';
        $Question = $Appointment->getQuestion;

        if ($didHappen == 'yes') {
            $Appointment->State = "completed";
            $Question->Report = $report;

            ContactLog::create([
                'ContactType' => 'appointment_' . $Question->AppointmentType,
                'PersonId' => $Question->Person->Id,
                'Reference' => $Appointment->Id,
                'DateTime' => date("Y-m-d H:i:s")
            ]);

            $slackState = "success";
        } else if ($didHappen == 'no_other') {
            $Appointment->State = "failedNoOther";
            $Appointment->CancelReason = $failReason;
            $slackState = "failed with other reason";
        } else if ($didHappen == 'no_notAvailable') {
            $Appointment->State = "failedNoShow";
            $slackState = "failed because of no show";
        }
        $result = $Appointment->save();
        $Question->State = "completed";
        $Question->save();
        app('App\Http\Controllers\StatisticsController')->log("question_" . $Question->AppointmentType, $Appointment->getAcceptedByPerson, ["SubEvent" => "reported", "EventRef" => $Question->Id, "team" => $Question->Person->TeamCareGroupId]);

        return $result;
    }

    /**
     * Cancel question funtionality for Client and Caregiver
     * @param Question $question
     * @param $AppointmentId
     * @param $cancelBy
     * @return bool
     */
    public function cancelQuestion(Question $question, $AppointmentId, $cancelBy = false)
    {
        if ($cancelBy == 'client') {
            $Appointment = Appointment::where(['Id' => $AppointmentId, 'QuestionId' => $question->Id])->first();
            $question->State = "Completed";
            $question->save();
            $Appointment->State = "canceledByClient";
            $Appointment->save();
            app('App\Http\Controllers\StatisticsController')->log("question_" . $question->AppointmentType, $question->Person, ["SubEvent" => "canceled_by_client", "EventRef" => $question->Id]);
        } elseif ($cancelBy == 'caregiver') {
            $Appointment = Appointment::where(['Id' => $AppointmentId, 'QuestionId' => $question->Id])->first();
            $Appointment->State = 'canceledByCaregiver';
            $Appointment->save();
            $question->CreateQuestionApointment();
            app('App\Http\Controllers\StatisticsController')->log("question_" . $question->AppointmentType, $Appointment->getAcceptedByPerson, ["SubEvent" => "canceled_by_caregiver", "EventRef" => $question->Id, "team" => $question->Person->TeamCareGroupId]);
        }
        return true;
    }

    /**
     * Client-Caregiver report question
     * @param Question $question
     * @param $didHappen
     * @param $report
     * @param $failReason
     * @return void
     */
    public function reportQuestion(Question $question, $didHappen, $report, $failReason)
    {
        $Appointment = Appointment::where(['State' => 'created', 'QuestionId' => $question->Id])->first();
        $slackState = '';

        if ($didHappen == 'yes') {
            $Appointment->State = "completed";
            $question->Report = $report;
            $slackState = "success";

            ContactLog::create([
                'ContactType' => 'appointment_' . $question->AppointmentType,
                'PersonId' => $question->Person->Id,
                'Reference' => $Appointment->Id,
                'DateTime' => date("Y-m-d H:i:s")
            ]);

        } else if ($didHappen == 'no_other') {
            $Appointment->State = "failedNoOther";
            $Appointment->CancelReason = $failReason;
            $slackState = "failed with other reason";
        } else if ($didHappen == 'no_notAvailable') {
            $Appointment->State = "failedNoShow";
            $slackState = "failed because of no show";
        }
        $question->State = "completed";
        $question->save();
        $Appointment->save();

        app('App\Http\Controllers\StatisticsController')->log("question_" . $question->AppointmentType, $Appointment->getAcceptedByPerson, ["SubEvent" => "reported", "EventRef" => $question->Id, "team" => $question->Person->TeamCareGroupId]);

    }
}
