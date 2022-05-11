<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Question;
use App\Models\Person;
use http\Env\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NewQuestionCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newQuestion:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New question cron';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request) {
        if(! empty($questionsThatNeedNotifications = $this->getQuestionsThatNeedNotifications() )) {
            foreach($questionsThatNeedNotifications as $question){
                $personsToNotifyIds = array();
                $personsToNotify = array();
                $person = $question->Person;
                $labels = $question->QuestionCareGroupsLabels->pluck('Name')->all();

                if(in_array("PersonalCareGivers", $labels)){
                    if($person->PersonalCareGroupId!=null){
                        foreach($person->PersonalCareGiverCareGroup->Persons as $p){
                            if(!in_array($p->Id, $personsToNotifyIds)){
                                array_push($personsToNotifyIds, $p->Id);
                                array_push($personsToNotify, $p);
                            }
                        }
                    }
                }

                if(in_array("CareGiverTeam", $labels)){
                    if($person->TeamCareGroup != null){
                        foreach($person->ClientTeam->Persons as $p){
                            if(!in_array($p->Id, $personsToNotifyIds)){
                                array_push($personsToNotifyIds, $p->Id);
                                array_push($personsToNotify, $p);
                            }
                        }
                    }
                }

                if(in_array("ClientCaregiverTeam", $labels)){
                    if($person->TeamCareGroup != null){
                        foreach($this->getClientCareGivers($person->TeamCareGroup) as $p){
                            if(!in_array($p->Id, $personsToNotifyIds) && $p->Id!=$person->Id){
                                array_push($personsToNotifyIds, $p->Id);
                                array_push($personsToNotify, $p);
                            }
                        }
                    }
                }
                $messageTemplate = MessageTemplate::where('Name', '=', 'newquestionnotification_v1')->firstOrFail();
                foreach($personsToNotify as $p){
                    $message = Message::create([
                       "MessageTemplateId" => $messageTemplate->Id,
                       "PersonId" => $p->Id,
                       "Variables" => json_encode([
                            "URL"=>getenv("protocol")."://".getenv("domain")
                       ]),
                       "Medium" => "SMS"
                    ]);
                    $message->send();
                }

                $question->update(['NotifiedGroups' => true]);
            }
        }

        if(intval(date("i"))<2 && intval(date("H"))==12){
            postSlackMessage("Worker is working > ".date("Y-m-d H:i:s"), ":clock1:", ":robot_face:", false);
        }

        if(getAmsterdamTimeZone()->format("H:i")=="17:00" || $request->get('forceDayOverview')){
            echo "<h1>Start daily calendar alert</h1>";
            foreach($this->getActiveCaregivers() as $person){
                $appointments = $person->getAppointments(["datetimeMinimum"=>date("Y-m-d 00:00:00", strtotime("+1 day")),"datetimeMaximum"=>date("Y-m-d 23:59:59", strtotime("+1 day"))]);
                if(count($appointments)>0){
                    $messageTemplate = MessageTemplate::where('Name', '=', 'dayoverview_v1')->firstOrFail();
                    $message = Message::create([
                       "MessageTemplateId" => $messageTemplate->Id,
                       "PersonId" => $person->Id,
                       "Variables" => json_encode([
                          "APPOINTMENTS"=>count($appointments)." ".((count($appointments)==1) ? "afspraak" : "afspraken"),
                          "URL"=>getenv("protocol")."://".getenv("domain")
                       ]),
                       "Medium" => "SMS"
                    ]);
                    $message->send();
                }
            }
        }
        file_put_contents("status.json", "{'ok':true}");
    }

    private function getQuestionsThatNeedNotifications() {
        return Question::with('Appointments')
            ->where('NotifiedGroups', 0)
            ->where('DateTimeCreated', '<', date("Y-m-d H:i:s", time()-5*60))
            ->get()->all();
    }

    private function getClientCareGivers($teamId) {
        return Person::query()
               ->where('IsClientCareGiver', 1)
               ->where('TeamCareGroupId', $teamId)
               ->where('IsActive', 1)
               ->get()->all();
    }

    private function getActiveCaregivers() {
        return Person::query()
             ->where('IsCareGiver', 1)
             ->where('IsActive', 1)
             ->get()->all();
    }
}
