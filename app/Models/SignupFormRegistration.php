<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignupFormRegistration extends Model
{
    use HasFactory;

    protected $table = 'SignupFormRegistration';
    protected $primaryKey = 'Id';
    public $timestamps = false;
}
