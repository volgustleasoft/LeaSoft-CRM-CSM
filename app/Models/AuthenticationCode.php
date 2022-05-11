<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticationCode extends Model
{
    use HasFactory;

    protected $table = 'AuthenticationCode';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['PersonId', 'Code', 'MagicToken', 'FailedAttemptsCount', 'IsUsed'];


    public function generateMagicToken(){
        $unique = false;

        while($unique===false){
            $characters = '2hi3fh1oi2fj3io23jg12i3j12fio312o3ijkmd232323';
            $charactersLength = strlen($characters);
            $token = '';
            for ($i = 0; $i < 7; $i++) {
                $token .= $characters[random_int(0, $charactersLength - 1)];
            }

            $unique = true;
            $query = self::query()
                 ->select('Id')
                 ->where('MagicToken', '=', $token)
                 ->where('DateTimeCreated', '>', date("Y-m-d 00:00:00", strtotime("-1 year")));

            if(! empty ($query->get()->first())) {
             $unique = false;
            }
        }

        return $token;
    }

    public function generateCode($Person, $NotOlderThan = false){
        $code = random_int(100000,999999)."";
        $alreadyExists = false;

        $query = self::query()
            ->select('Id')
            ->where('PersonId', '=', $Person->Id)
            ->where('Code', '=', $code)
            ->where('IsUsed', '=', 0)
            ->when($NotOlderThan, function ($query) use ($NotOlderThan) {
                return $query->where('DateTimeCreated', '>', $NotOlderThan);
            });

        if(! empty ($query->get()->first())) {
            $alreadyExists = true;
        }

        if($alreadyExists===true){
            $code = $this->generateCode($Person);
        }

        return $code;
    }

    public function getCodeMadeRecently($personId) {
        return self::query()
            ->where('PersonId', '=', $personId)
            ->where('DateTimeCreated', '>', date("Y-m-d H:i:s", time()-60*intval(getenv("authentication_code_accept_renew_time_in_minutes"))))
            ->where('IsUsed', '=', 0)
            ->where('FailedAttemptsCount', '<', 3)
            ->get()->first();
    }
}
