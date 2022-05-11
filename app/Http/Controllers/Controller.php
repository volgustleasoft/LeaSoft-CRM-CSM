<?php

namespace App\Http\Controllers;

use App\Http\Traits\AuthTrait;
use App\Http\Traits\PincodeTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, AuthTrait, PincodeTrait;

    protected $request;
    protected $person;
    protected $error;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->person = Auth::check() ? Auth::user() : null;
    }

    public function redirectTo() {
        if($this->person->IsCareGiver){
            return redirect('/agenda/caregiver');
        }else{
            return redirect('/agenda/client');
        }
    }
}
