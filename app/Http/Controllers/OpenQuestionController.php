<?php

namespace App\Http\Controllers;

use App\Http\Traits\DCTrait;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Person;
use App\Models\Question;
use App\Models\QuestionCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class OpenQuestionController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function assign(Request $request)
    {
        $postData = $this->request->post()['params'];
        if (!empty($postData['year']) &&
            !empty($postData['month']) &&
            !empty($postData['day']) &&
            !empty($postData['hour']) &&
            !empty($postData['minute']) &&
            !empty($postData['careGiver']) &&
            !empty($postData['question_id'])) {

            $Appointment = Appointment::where('QuestionId', $postData['question_id'])->where('State', 'new')->first();
            $Caregiver = Person::find($postData['careGiver']);
            $question = Question::find($postData['question_id']);
            $StartDateTime = Carbon::create($postData['year'], $postData['month'], $postData['day'], $postData['hour'], $postData['minute'], '00', getAmsterdamTimeZoneFormat());

            if ($question->AppointmentType == "call") {
                $EndDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime, getAmsterdamTimeZoneFormat())->addMinutes(30);
            } else {
                $EndDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime, getAmsterdamTimeZoneFormat())->addMinutes(60);
            }

            if ($this->person->IsCareGiver == 1) {
                $result = $Appointment->update([
                    'State' => 'created',
                    'AcceptedByPersonId' => $Caregiver->Id,
                    'DateTimeAppointmentFrom' => getUTC($StartDateTime),
                    'DateTimeAppointmentTo' => getUTC($EndDateTime)
                ]);
            }

                $messageTemplate = MessageTemplate::where('Name', "appointmentconfirmclient_v1")->first();

                $ClientMessage = Message::create([
                    "MessageTemplateId" => $messageTemplate->Id,
                    "PersonId" => $question->Person->Id,
                    "Variables" => json_encode([
                        "CAREGIVER" => $Caregiver->Firstname . " " . $Caregiver->Lastname,
                        "DATETIME" => getAmsterdamTimeZone($StartDateTime)->format("d-m-Y H:i")
                    ]),
                    "Medium" => "SMS"
                ]);

            $ClientMessage->send();
            postSlackMessage("Sent message *" . $messageTemplate->Name . "* to person *" . $question->Person->Id . "* via *SMS*", ":envelope:");
            CareAnalytics("message_sms", $question->Person->Id, ['SubEvent' => $messageTemplate->Name]);
            postSlackMessage(
                "Appointment created for question *" . $question->Id . "*  for *" .
                getAmsterdamTimeZone($StartDateTime)->format("Y-m-d H:i:s") .
                "* by caregiver *" . $Caregiver->Id . "* Person *" .
                $this->person->Id . "* assigned this appointment", ":calendar:"
            );

            $request->session()->put('message', [
                "text" => __("The question is scheduled."),
                "type" => "success"
            ]);

            return response()->json(['success' => $result]);
        }
    }

    public function showCaregiverView()
    {
        $this->authorize('showCaregiverView', $this->person);

        $caregiverTeams = $this->person->CareGroupsCaregiver(true)->get()->keyBy('Id');
        $TeamsOpenQuestion = new Collection;
        foreach ($caregiverTeams as $team){
            if ($team->CareGroupLabelId == 1){
                $SearchBy = 'caregiver';
            } elseif ($team->CareGroupLabelId == 2){
                $SearchBy = 'personalcaregiver';
            }
            $openQuestions = $team->getOpenQuestion($SearchBy)->all();
            $TeamsOpenQuestion = $TeamsOpenQuestion->merge($openQuestions);
        }

        $sortedQuestions = $TeamsOpenQuestion->SortBy([
            ['DateTimeFrom', 'asc'],
        ]);

        foreach ($TeamsOpenQuestion as $question){
            $this->QuestionDataRendering($question);
            $question->Team = $question->client->ClientTeam->Name;
        }

        return view('openQuestion-caregiver', [
            "Caregivers" => $this->person,
            "OpenQuestions" => $sortedQuestions,
            'message' => ! empty(session('message')) ? json_encode(session('message')) : '{}',
            'assetUrl' => asset('/img')
        ]);
    }

    /**
     * @param $question
     */
    public function QuestionDataRendering($question): void
    {
        $QuestionSubCategory = QuestionCategory::where('Id', $question->QuestionCategory->ParentId)->first();
        $question->client = Person::find($question->PersonId);
        $question->AssignedRoles = $question->GetRolesFromQuestion();
        $question->Categories = !empty($QuestionSubCategory) ? $QuestionSubCategory->Name . " > " . $question->QuestionCategory->Name : $question->QuestionCategory->Name;
        $question->DateTimeFrom = getAmsterdamTimeZone($question->DateTimeFrom)->format('D, d M Y H:i:s');
        $question->DateTimeTo = getAmsterdamTimeZone($question->DateTimeTo)->format('D, d M Y H:i:s');
    }
}
