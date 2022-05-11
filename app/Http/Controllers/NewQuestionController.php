<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\Redirect;
use App\Models\CareGroup;
use App\Models\CareGroupLabel;
use App\Models\Person;
use App\Models\QuestionCareGroupLabel;
use App\Models\Question;


class NewQuestionController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        if(empty($request->session()->get('AddQuestion'))
            and $this->request->url() !== route('questionCategory')
            and $this->request->url() !== route('questionCancel')){
            return $this->checkRouting();
        }
    }

    public function ajaxPostHandler() {
        return response()->json(call_user_func(array($this, $this->request->post('action'))));
    }

    public function newQuestionCategoryView() {

        $this->authorize('showClientView', $this->person);

        if($this->request->url() !== route('editQuestionCategory')) $this->forgetNewQuestionSession();
        {
            $hoursData = $this->getTeamHoursData();
            if($this->isWorkingTimeNow($hoursData)) return redirect(route('agendaClient'));
        }

        return view('question/category', [
            "categories" => $this->getCategories()['data'],
            "currentValue" => $this->request->session()->get('AddQuestion.QuestionCategoryId') ?? null,
        ]);
    }

    public function newQuestionCategoryAction() {
        $categories = $this->getCategories();

        if(in_array($this->request->QuestionCategoryId, $categories['ids'])) {
            $this->request->session()->put('AddQuestion.QuestionCategoryId', $this->request->QuestionCategoryId);
            return $this->checkRouting();
        }
    }

    public function newQuestionDescriptionView() {
        $this->authorize('showClientView', $this->person);
        if(empty($this->request->session()->get('AddQuestion.QuestionCategoryId'))) return $this->checkRouting();

        return view('question/description', [
            "currentValue" => $this->request->session()->get('AddQuestion.Description') ?? null,
        ]);
    }

    public function newQuestionDescriptionAction() {
        if(strlen($this->request->Description)>5){
            $this->request->session()->put('AddQuestion.Description', $this->request->Description);
            return $this->checkRouting();
        }
    }

    public function newQuestionAppointmentTypeView() {
        $this->authorize('showClientView', $this->person);
        if(empty($this->request->session()->get('AddQuestion.Description'))) return $this->checkRouting();


        return view('question/appointmentType', [
            "currentTypeValue"=> empty($this->request->session()->get('AddQuestion.Type')) ? null : $this->request->session()->get('AddQuestion.Type'),
            "currentGroupValue"=> empty($this->request->session()->get('AddQuestion.PersonGroupIds')) ? null : $this->request->session()->get('AddQuestion.PersonGroupIds'),
            "availablePersonGroups" => $this->getAvailablePersonGroups(),
            "error" => ! empty($this->request->session()->get('error')) ? $this->request->session()->get('error') : '',
        ]);
    }

    public function newQuestionAppointmentTypeAction() {

        $appointmentType = $this->request->AppointmentType;

        if(! in_array($appointmentType, ['visit', 'call'])) {
            $this->request->session()->put('error', 'Invalid appointment type');
            return $this->checkRouting();
        }

        if(empty($personGroups = $this->request->availablePersonGroup)) {
            $this->request->session()->put('error', 'No person group selected');
            $this->request->session()->forget('AddQuestion.Type');
            return $this->checkRouting();
        }

        $availablePersonGroupIds = array();
        $category = QuestionCategory::find($this->request->session()->get('AddQuestion.QuestionCategoryId'));
        foreach($category->getCareGroupLabels() as $label){
            array_push($availablePersonGroupIds, $label->Id);
        }

        for($i=0;$i<count($personGroups);$i++){ $personGroups[$i]=intval($personGroups[$i]); }

        $validPersonGroupSelection = true;
        foreach($personGroups as $personGroupId){
            if(!in_array($personGroupId, $availablePersonGroupIds)){
                $validPersonGroupSelection=false;
            }
        }

        if($validPersonGroupSelection){
            $this->request->session()->forget('error');
            if($this->request->session()->get('AddQuestion.Type') != $appointmentType){
                $this->request->session()->put('AddQuestion.AvailableTimeSlot', null);
            }
            $this->request->session()->put('AddQuestion.Type', $appointmentType);
            $this->request->session()->put('AddQuestion.PersonGroupIds', $personGroups);
            return $this->checkRouting();
        }
    }

    public function newQuestionDateTimeView(){
        $this->authorize('showClientView', $this->person);
        if(empty($this->request->session()->get('AddQuestion.Type'))) return $this->checkRouting();

        $dates = $this->getDates();

        return view('question/dateTime', [
            "month"=>$this->getMonth(),
            "year"=>$this->getYear(),
            "firstDateOfMonth"=>$this->getFirstDateOfMonth(),
            "firstDateOfPreviousMonth"=>$this->getFirstDateOfPreviosMonth(),
            "firstDateOfNextMonth"=>$this->getFirstDateOfTheNextMonth() ?? null,
            "lastDateInMonth"=>$this->getLastDateInMonth()?? null,
            "selectedDate"=>$this->getSelectedDate(),
            "nextDate"=>$this->getNextDate(),
            "previousDate"=> $this->getPreviousDate(),
            "selectedDateShort"=>strftime("%d %b %a",$this->getSelectedDate()->getTimestamp()),
            "translatedMonth"=>strftime("%B",$this->getSelectedDate()->getTimestamp()),
            "todayDate"=>date("Y-m-d"),
            "dates"=>$dates,
            "assetUrl" => asset('/img'),
            'message' => ! empty($this->request->session()->get('message')) ? $this->request->session()->get('message'): null,
        ]);
    }

    public function newQuestionDateTimeAction() {
            if(empty($this->request->timeslot)) return $this->checkRouting();

            $dates = $this->getDates();
            $validTime = false;
            foreach ($dates[$this->getSelectedDate()->format("Y-m-d")] as $timeblock) {
                if ($timeblock['time'] == $this->request->timeslot && $timeblock['available']) {
                    $validTime = true;
                }
            }

            if (! $validTime) return $this->checkRouting();

            $datetime = new \DateTime($this->getSelectedDate()->format("Y-m-d") . " " . $this->request->timeslot . ":00", new \DateTimeZone(getAmsterdamTimeZoneFormat()));
            $this->request->session()->put('AddQuestion.AvailableTimeSlot', $datetime);
            return $this->checkRouting();

    }

    public function newQuestionSummaryView(){
        $this->authorize('showClientView', $this->person);
        if(empty($this->request->session()->get('AddQuestion.AvailableTimeSlot'))) return $this->checkRouting();

        $categoryTwig = null;
        $subcategoryTwig = null;

        $type = $this->request->session()->get('AddQuestion.Type');
        $datetime = $this->request->session()->get('AddQuestion.AvailableTimeSlot');
        $durationInSeconds = ($type=="call") ? 30*60 : 60*60;
        $description = $this->request->session()->get('AddQuestion.Description');
        $category = QuestionCategory::find($this->request->session()->get('AddQuestion.QuestionCategoryId'));
        $datetimeEnd = getAmsterdamTimeZone(date("Y-m-d H:i:s", $this->request->session()->get('AddQuestion.AvailableTimeSlot')->getTimestamp()+$durationInSeconds));

        if (! empty($parentCategory = $category->getParentQuestionCategory())) {
            $categoryTwig = $parentCategory->Name;
            $subcategoryTwig = $category->Name;
        } else {
            $categoryTwig = $category->Name;
            $subcategoryTwig = "Overig";
        }

            $personGroupsTwig = null;
            foreach($this->request->session()->get('AddQuestion.PersonGroupIds') as $labelId){
                $label = CareGroupLabel::find($labelId);
                if($personGroupsTwig!=null){ $personGroupsTwig.= ", "; }
                switch ($label->Name) {
                    case "CareGiverTeam":
                        $personGroupsTwig .= "Begeleiders";
                        break;
                    case "PersonalCareGivers":
                        $personGroupsTwig .= "Mijn regievoerders";
                        break;
                    case "ClientCaregiverTeam":
                        $personGroupsTwig .= "Mede cliënten";
                        break;
                }
            }

        return view('question/summary', [
            "description"=> $description,
            "type"=> $type,
            "category"=>$categoryTwig,
            "subcategory"=>$subcategoryTwig,
            "personGroups" => $personGroupsTwig ?? null,
            "datetime"=>$datetime,
            "datetimeEnd"=>$datetimeEnd,
            "datePretty"=>strftime("%A %d %B %Y",$datetime->getTimestamp()),
        ]);
    }

    public function newQuestionSummaryAction(){
            $availableTimeSlot = getUTC($this->request->session()->get('AddQuestion.AvailableTimeSlot')->format('Y-m-d H:i:s'));
            $datetimeZone = $this->request->session()->get('AddQuestion.AvailableTimeSlot');

            $question = Question::create([
                 "PersonId" => $this->person->Id,
                 "QuestionCategoryId" => $this->request->session()->get('AddQuestion.QuestionCategoryId'),
                 "Description" => $this->request->session()->get('AddQuestion.Description'),
                 "AppointmentType" => $this->request->session()->get('AddQuestion.Type'),
                 "ExternalLocationAllowed" => true,
                 "State" => 'InProgress'
             ]);

             app('App\Http\Controllers\StatisticsController')->log("question_".$question->AppointmentType, $this->person, ["SubEvent"=>"create", "EventRef"=>$question->Id]);

            $timeslotDuration = 15;
            switch ($this->request->session()->get('AddQuestion.Type')) {
                case "call";
                    $timeslotDuration = 30;
                    break;
                case "visit";
                    $timeslotDuration = 60;
                    break;
            }

            $question->setAvailableTimeSlot($availableTimeSlot, $timeslotDuration);
            $question->CreateQuestionApointment();

            postSlackMessage("New question added with id *" . $question->Id . "*", ":sparkles:", ":robot_face:", false);

            $warnTeam = false;
            $questionText = strtolower($this->request->session()->get('AddQuestion.Description'));
            foreach($this->getWarningWords() as $word){
                if(strpos($questionText, $word)>0){
                    $warnTeam = true;
                }
            }
            if($warnTeam===true){
                postSlackMessage("Please check question id *".$this->Id."*, because the content may be urgent.", ":rotating_light:", ":robot_face:", true);
            }

            foreach(QuestionCategory::find($this->request->session()->get('AddQuestion.QuestionCategoryId'))->getCareGroupLabels() as $label){
                if(in_array($label->Id, $this->request->session()->get('AddQuestion.PersonGroupIds'))){
                    $isSelected = true;
                }else{
                    $isSelected = false;
                }
                QuestionCareGroupLabel::create([
                   'QuestionId' => $question->Id,
                   'CareGroupLabelId' => $label->Id,
                   'IsSelected' => $isSelected
                ]);
            }

            $this->request->session()->put('QuestionCategory', QuestionCategory::find($this->request->session()->get('AddQuestion.QuestionCategoryId')));
            $this->request->session()->put('AddQuestion.QuestionId', $question->Id);

            return redirect('/question/done');

    }

    public function newQuestionDoneView() {
        $this->authorize('showClientView', $this->person);
        if(empty($this->request->session()->get('AddQuestion.QuestionId'))) return $this->checkRouting();

        $team = CareGroup::find($this->person->TeamCareGroupId);

        if (is_null($this->request->session()->get('AddQuestion'))){
            return redirect('/');
        }


        return view('question/done', [
           "assetUrl" => asset('/img'),
        ]);
    }

    public function newQuestionCancelAction() {
        $this->forgetNewQuestionSession();

        return redirect('/agenda/client');
    }

    public function forgetNewQuestionSession() {
        $this->request->session()->forget('AddQuestion');
        $this->request->session()->forget('message');
        $this->request->session()->forget('QuestionCategory');
        $this->request->session()->forget('error');
    }

    public function getWarningWords(){
        return array("dood","moord","zelfmoord", "bloed");
    }

    public function getCategories() {
        $categories = array();
        $validIds = array();
        foreach(QuestionCategory::whereNull('ParentId')->get()->all() as $rootCategory){
            $childCategories = array();

            if(! empty($childCategoreis = $rootCategory->getChildrenQuestionCategories()) ) {
                foreach($childCategoreis as $childCategory){
                    array_push($childCategories, [
                        "Id" => $childCategory->Id,
                        "Name" => $childCategory->Name,
                        "Icon" => $childCategory->Icon
                    ]);
                    array_push($validIds, $childCategory->Id);
                }
            }

            array_push($categories, [
                "Id"=>$rootCategory->Id,
                "Name"=>$rootCategory->Name,
                "Icon"=>$rootCategory->Icon,
                "Childs"=>$childCategories
            ]);
            array_push($validIds, $rootCategory->Id);
        }

        return [
            'data' => $categories,
            'ids' => $validIds
        ];
    }

    public function checkRouting($page=null){
        if(empty($this->request->session()->get('AddQuestion.QuestionCategoryId')) && $page!="category"){
            return redirect('/question/category');
        }
        if(empty($this->request->session()->get('AddQuestion.Description')) && $page!="description"){
            return redirect('/question/description');
        }
        if(empty($this->request->session()->get('AddQuestion.Type')) && $page!="type"){
            return redirect('/question/appointmentType');
        }
        if(empty($this->request->session()->get('AddQuestion.AvailableTimeSlot')) && $page!="datetime"){
            return redirect('/question/dateTime');
        }
        if($page !== "summary"){
            return redirect('/question/summary');
        }

        return true;
    }

    private function getAvailablePersonGroups(){

        $category = QuestionCategory::find($this->request->session()->get('AddQuestion.QuestionCategoryId'));
        $availablePersonGroups = array();
        foreach($category->getCareGroupLabels() as $label){
            array_push($availablePersonGroups, ["Id"=>$label->Id, "Name"=>$label->Name]);
        }

        return $availablePersonGroups;
    }
}
