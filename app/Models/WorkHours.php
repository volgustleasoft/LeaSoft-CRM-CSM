<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkHours extends Model
{
    use HasFactory;

    protected $table = 'WorkHours';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Hours', 'DayNumber', 'AppointmentType', 'TeamCareGroupId'];
}
