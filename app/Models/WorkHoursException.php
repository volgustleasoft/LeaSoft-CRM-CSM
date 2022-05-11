<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkHoursException extends Model
{
    use HasFactory;

    protected $table = 'WorkHoursException';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Hours', 'ExceptionDate', 'AppointmentType', 'TeamCareGroupId'];
}
