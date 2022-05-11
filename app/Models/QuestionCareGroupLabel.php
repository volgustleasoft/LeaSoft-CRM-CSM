<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCareGroupLabel extends Model
{
    use HasFactory;

    protected $table = 'QuestionCareGroupLabel';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['QuestionId', 'CareGroupLabelId', 'IsSelected'];
}
