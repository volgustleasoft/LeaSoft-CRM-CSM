<?php

namespace App\Http\Controllers;

use App\Models\CareGroup;
use App\Models\CareGroupPerson;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ClientenController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function showCaregiverView()
    {
        $this->authorize('showCaregiverView', $this->person);

        $caregiverTeams = $this->person->CareGroupsCaregiver->pluck('Id')->toArray();

        $clients = $this->person->getClientsForPerson($caregiverTeams);
        foreach ($clients as $client){
            $client->teamName = CareGroup::select('Name')->where('Id', $client->TeamCareGroupId)->first()->Name;
            $client->IsPersonalClient = $client->IsPersonalClient($this->person);
        }

        $sortedClients = $clients->sortBy('IsPersonalClient')->reverse();

        return view('clienten-caregiver', [
            "persons" => $sortedClients,
            'assetUrl' => asset('/img')
        ]);
    }
}
