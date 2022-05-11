<?php

namespace App\Http\Controllers;

use App\Models\CareGroupPerson;
use App\Models\Person;
use Illuminate\Http\Request;

class PersonalCaregiverController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function removeCG()
    {
        $postData = $this->request->post()['params'];
        $PersonalCaregiver = CareGroupPerson::find($postData['id']);
        $Client = Person::query()->where('PersonalCareGroupId', $PersonalCaregiver->CareGroupId)->first();
        $result = $PersonalCaregiver->delete();

        postSlackMessage("Manager *". $this->person->Id."* removed caregiver *".$PersonalCaregiver->PersonId."* as personal caregiver for client *".$Client->Id."*", ":female-doctor::wastebasket:");

        return response()->json(['success' => $result]);
    }

    public function addCG()
    {
        $postData = $this->request->post()['params'];
        $Client = Person::find($postData['clientId']);
        $Caregiver = Person::find($postData['caregiverId']);
        if(count($Client->ClientTeam->CareGroupPersons->where("PersonId", $Caregiver->Id))==1){
            $careGroupPerson = new CareGroupPerson();
            $careGroupPerson->PersonId = $Caregiver->Id;
            $careGroupPerson->CareGroupId = $Client->PersonalCareGroupId;
            $result = $careGroupPerson->save();

            postSlackMessage("Manager *" . $this->person->Id . "* added caregiver *" . $Caregiver->Id . "* as personal caregiver for client *" . $Client->Id . "*", ":female-doctor::heavy_plus_sign:");
        }

        return response()->json(['success' => $result]);
    }

    public function showClientenPage(Request $request)
    {
        $this->authorize('showAdminView', $this->person);

        $client = Person::find(intval($request->Id));

        $personalCaregivers = $client->PersonalCareGiverCareGroup->CaregiversFromPersonTeam()->keyBy('Id');
        foreach ($personalCaregivers as $personalCaregiver){
            $personalCaregiver->ClientPersonTeam = $client->ClientCaregiver->where('PersonId', $personalCaregiver->Id)->where('CareGroupId', $client->PersonalCareGroupId)->first()->Id;
        }

        $listOfAvailableGaregivers = $client->ClientTeam->CareGroupPersons;
        $availableCareGivers = array();
        foreach($listOfAvailableGaregivers as $caregiver){
            if(!array_key_exists($caregiver->PersonId, $personalCaregivers->toArray())){
                $caregiver =  Person::find($caregiver->PersonId);
                array_push($availableCareGivers, $caregiver);
            }
        }

        return view('personalCaregiver', [
            "availableCareGivers" => $availableCareGivers,
            "personalCareGivers" => $personalCaregivers,
            "client"=>[
                "Firstname"=>$client->Firstname,
                "Lastname"=>$client->Lastname,
                "Id"=>$client->Id
            ],
            'assetUrl' => asset('/img')
        ]);
    }
}
