<?php

namespace App\Http\Controllers;

use App\Http\Traits\AgendaTrait;
use App\Http\Traits\AppointmentTrait;
use App\Models\Appointment;
use App\Models\CareGroup;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Person;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AgendaController extends Controller
{
    use AppointmentTrait, AgendaTrait;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function ajaxPostHandler(Request $request)
    {
        return response()->json(call_user_func(array($this, $request->post('action')), $request));
    }

    public function ajaxHandler(Request $request)
    {
        return response()->json(call_user_func(array($this, $request->input('action'))));
    }

    /**
     *
     * Client Cansel Question action
     * @param Request $request
     * @return false[]
     */
    public function cancelQuestionAction(Request $request)
    {

        $question = Question::find($this->request->post('questionId'));
        $AskedByPerson = Person::find($this->request->post('personId'));
        $Appointment = Appointment::find($this->request->post('appointmentId'));
        $result = false;

        if ($question->PersonId == $AskedByPerson->Id && $this->person->Id == $Appointment->AcceptedByPersonId) {
            $result = $this->cancelQuestion($question, $Appointment->Id, 'caregiver');
            $messageTemplate = MessageTemplate::where('Name', '=', 'canceledappointmentclient_v1')->firstOrFail();
            $dateTime = getAmsterdamTimeZone();
            $dateTime->setTimestamp(strtotime($question->Appointments()->first()->DateTimeAppointmentFrom));

            $message = Message::create([
                "MessageTemplateId" => $messageTemplate->Id,
                "PersonId" => $AskedByPerson->Id,
                "Variables" => json_encode([
                    "CAREGIVER" => $this->person->Firstname . " " . $this->person->Lastname,
                    "DATETIME" => $dateTime->format("d-m H:i")
                ]),
                "Medium" => "SMS"
            ]);

            $message->send();

            postSlackMessage("Sent message *" . $messageTemplate->Name . "* to person *" . $AskedByPerson->Id . "* via *SMS*", ":envelope:");
            postSlackMessage("Appointment for question *" . $question->Id . "* by client *" . $this->person->Id . "* at *" . $dateTime->format("d-m-Y H:i") . "* is canceled by *caregiver*. Rescheduling now.", ":nope:");


        } elseif ($question->PersonId == $AskedByPerson->Id) {
            $result = $this->cancelQuestion($question, $Appointment->Id, 'client');

            // Stop DC flow
            $this->stopDirectContactFlow($question->Id);

            if (!empty($Appointment->AcceptedByPersonId)) {
                $Caregiver = Person::find($Appointment->AcceptedByPersonId);
                $messageTemplate = MessageTemplate::where('Name', '=', 'canceledappointmentcaregiver_v1')->firstOrFail();
                $dateTime = getAmsterdamTimeZone();
                $dateTime->setTimestamp(strtotime($question->Appointments()->first()->DateTimeAppointmentFrom));

                $message = Message::create([
                    "MessageTemplateId" => $messageTemplate->Id,
                    "PersonId" => $Caregiver->Id,
                    "Variables" => json_encode([
                        "CLIENT" => $AskedByPerson->Firstname . " " . $AskedByPerson->Lastname,
                        "DATETIME" => $dateTime->format("d-m H:i")
                    ]),
                    "Medium" => "SMS"
                ]);

                $message->send();
                postSlackMessage("Sent message *" . $messageTemplate->Name . "* to person *" . $Caregiver->Id . "* via *SMS*", ":envelope:");
            }
            $dateTime = !empty($dateTime) ? $dateTime->format("d-m-Y H:i") : "No time";

            postSlackMessage("Appointment for question *" . $question->Id . "* by client *" . $AskedByPerson->Id . "* at *" . $dateTime . "* is canceled by *client*. Closed the question.", ":nope:");
        }

        if ($result) {
            $request->session()->put('message', [
                "text" => __("The Question was canceled"),
                "type" => "success"
            ]);
        }

        return ['success' => $result];
    }

    /**
     * Caregiver cancel Appointment action
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelAppointmentAction(Request $request)
    {

        $Appointment = Appointment::find($this->request->post('appointmentId'));
        $Question = $Appointment->getQuestion;
        $result = false;

        if ($this->person->Id == $Appointment->AcceptedByPersonId) {
            $result = $this->cancelAppointment($Appointment, null);
            $dateTime = getAmsterdamTimeZone();
            $dateTime->setTimestamp(strtotime($Appointment->DateTimeAppointmentFrom));

            $messageTemplate = MessageTemplate::where('Name', '=', 'canceledappointmentclient_v1')->firstOrFail();
            $Client = $Question->Person;
            $message = new Message([
                "MessageTemplateId" => $messageTemplate->Id,
                "PersonId" => $Client->Id,
                "Variables" => json_encode([
                    "CAREGIVER" => $this->person->Firstname . " " . $this->person->Lastname,
                    "DATETIME" => $dateTime->format("d-m H:i")
                ]),
                "Medium" => "SMS"
            ]);
            $message->send();
            postSlackMessage("Appointment for question *" . $Question->Id . "* by client *" . $Client->Id . "* at *" . $dateTime->format("d-m-Y H:i") . "* is canceled by *caregiver*. Rescheduling now.", ":nope:");

        }

        return response()->json(['success' => $result]);
    }

    /**
     * Core Caregiver Agenda loading page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function showCaregiverPage()
    {
        $this->authorize('showCaregiverView', $this->person);

        return view('/agenda/caregiver', [
            "error" => $this->error,
            'currentFilterChoice' => $this->request->type,
            'message' => !empty(session('message')) ? json_encode(session('message')) : '{}',
            'assetUrl' => asset('/img'),
        ]);
    }

    /**
     * Vue Action getting Dates for Agenda vue-date-picker calendar
     * @return array[]
     */
    public function getDatesForMonth()
    {
        $date = $this->request->date ? $this->request->date : date("Y-m");

        $dates = $counts = [];
        $appointments = new Collection();

        if ($this->request->has('selectedUser') && !empty($this->request->selectedUser)) {
            $caregiver = Person::find($this->request->selectedUser);
            $appointments = $appointments
                ->merge($caregiver->getAppointmentsForMonth($date, ['AcceptedByPersonId' => $caregiver->Id]));
        } else {
            $appointments = $appointments
                ->merge($this->person->getAppointmentsForMonth($date, !empty($currentTeamId) ? ['teamId' => $currentTeamId] : ['AcceptedByPersonId' => $this->person->Id]));
        }
        $careGivers = array();

        foreach ($appointments as $appointment) {
            $counts[$appointment->AcceptedByPersonId] = isset($counts[$appointment->AcceptedByPersonId]) ? $counts[$appointment->AcceptedByPersonId] + 1 : 1;
            $dates[$appointment->AcceptedByPersonId][] = getAmsterdamTimeZone($appointment->DateTimeAppointmentFrom)->format("Y-m-d");
            if (empty($careGivers[$appointment->AcceptedByPersonId])) {
                $careGivers[$appointment->AcceptedByPersonId] = $appointment->getAcceptedByPerson->Firstname . " " . $appointment->getAcceptedByPerson->Lastname;
            }
        }

        return [
            "counts" => $counts,
            "dates" => $dates,
            "Caregivers" => $careGivers
        ];
    }

    /**
     * Appointments data for Caregiver Agenda page
     * @return array
     */
    public function getCaregiverAppointmentForDate()
    {
        $date = $this->request->date ? $this->request->date : date("Y-m-d");

        $appointmentsData = new Collection();
        $appointments = new Collection();

        $appointments = $appointments
            ->merge($this->person->getAppointmentsForDate($date, ['AcceptedByPersonId' => $this->person->Id]));

        foreach ($appointments as $appointment) {
            $appointmentsData = $appointmentsData->push($this->prepare_appointment_data($appointment, true));
        }

        return [
            "appointments" => $appointmentsData,
            "activeDate" => $date
        ];
    }

    /**
     * Core Client Agenda loading page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showClientPage()
    {
        $this->authorize('showClientView', $this->person);

        $isAvailableDC = false;
        $isDayOff = false;

        if ($this->request->has('date') and $this->request->date == 'pending') {
            $openQuestion = $this->PendingClientAgenda();
        } elseif ($this->request->has('date') and $this->request->date == 'active') {
            $openQuestion = $this->ActiveClientAgenda();
        } else {
            $openQuestion = $this->ActiveClientAgenda();
        }

        $exceptionData = $this->getWorkingExceptionData();
        $hoursData = $this->getTeamHoursData();

        if (!$exceptionData['isWorkingExceptionNow']) {
            if (!empty($hoursData)) {
                $isAvailableDC = $this->isWorkingTimeNow($hoursData);
            } else {
                $isDayOff = true;
            }
        } else {
            if ($exceptionData['isDayOff']) {
                $isDayOff = true;
            }
        }

        return view('/agenda/client', [
            'PersonId' => $this->person->Id,
            "error" => '$this->error',
            'message' => !empty(session('message')) ? json_encode(session('message')) : '{}',
            "appointments" => $openQuestion,
            "workingTime" => ($isAvailableDC or !$isDayOff) ? $this->getWorkingTime($hoursData) : null,
            'currentFilterChoice' => $this->request->date,
            'assetUrl' => asset('/img'),
        ]);
    }

    /**
     * Appointments for active Client Agenda page
     * @return mixed
     */
    public function ActiveClientAgenda()
    {
        $openQuestions = new Collection();

        $regularQuestions = $this->person->getQuestions([
            "States" => ["InProgress"],
            "AppointmentType" => ['call', 'visit'],
            "NotPastQuestion" => true,
        ]);

        $openQuestions = $openQuestions
            ->merge($regularQuestions)
            ->keyBy('Id');

        foreach ($openQuestions as $question) {
            $cardClass = '';
            if (isset($question->AcceptedByPersonId) || isset($question->Person)) {

                $question->AcceptedByPerson = Person::find($question->AcceptedByPersonId);
                $question->AcceptedByPerson->role = $question->AcceptedByPerson->IsClientCareGiver ? "client" : "caregiver";
                $question->askedByPerson = Person::find($question->PersonId);
                $question->shouldReport = Appointment::where('QuestionId', $question->Id)->where('State', '!=', 'canceledByCaregiver')->first()->shouldReport($this->person);
                $question->initiator = $question->askedByPerson;

                if ($question->AcceptedByPersonId == $this->person->Id && !$question->shouldReport) {
                    $cardClass = ' green';
                } elseif ($question->AcceptedByPersonId == $this->person->Id && $question->shouldReport) {
                    $cardClass = ' red';
                } elseif ($question->IsDirectContact == 1) {
                    $cardClass = ' pink';
                }
                $question->cardClass = $cardClass;

                $question->Categories = !empty($QuestionSubCategory) ? $QuestionSubCategory->Name . " > " . $question->QuestionCategory->Name : $question->QuestionCategory->Name;
            }
        }
        return $openQuestions->SortBy([
            ['DateTimeAppointmentFrom', 'asc']
        ]);
    }

    /**
     * Appointments for pending Client Agenda page
     * @return mixed
     */
    public function PendingClientAgenda()
    {
        $regularQuestions = $this->person->getQuestions([
            "States" => ["InProgress", "new"],
            "AppointmentType" => ['call', 'visit']
        ], $this->request)->keyBy('Id');

        foreach ($regularQuestions as $question) {
            $cardClass = '';
            if (isset($question->Person)) {
                $question->askedByPerson = Person::find($question->PersonId);
                $question->initiator = $question->askedByPerson;
                $question->selectedRoles = $question->GetRolesFromQuestion();

                if (is_null($question->DateTimeAppointmentFrom)) {
                    $question->DateTimeAppointmentFrom = $question->QuestionAvailableTimeSlot->DateTimeFrom;
                    $question->DateTimeAppointmentTo = $question->QuestionAvailableTimeSlot->DateTimeTo;
                }

                if ($question->AcceptedByPersonId == $this->person->Id && !$question->shouldReport) {
                    $cardClass = ' green';
                } elseif ($question->IsDirectContact == 1) {
                    $cardClass = ' pink';
                }
                $question->cardClass = $cardClass;
                $question->Categories = !empty($QuestionSubCategory) ? $QuestionSubCategory->Name . " > " . $question->QuestionCategory->Name : $question->QuestionCategory->Name;
            }
        }

        return $regularQuestions->SortBy([
            ['DateTimeAppointmentFrom', 'asc'],
        ]);
    }
}
