<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionToken extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($SessionToken) {
            $characters = '123987DSFJSOIDFJJOODF233';
            $charactersLength = strlen($characters);
            $code = '';
            for ($i = 0; $i < 100; $i++) {
                $code .= $characters[random_int(0, $charactersLength - 1)];
            }
            $SessionToken->Token = $code;
        });
    }

    protected $table = 'SessionToken';
    protected $primaryKey = 'Id';
    public $timestamps = false;
}
