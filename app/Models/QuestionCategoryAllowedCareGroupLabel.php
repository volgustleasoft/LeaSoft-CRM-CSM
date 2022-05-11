<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCategoryAllowedCareGroupLabel extends Model
{
    use HasFactory;

    protected $table = 'QuestionCategoryAllowedCareGroupLabel';
    protected $primaryKey = 'Id';
    public $timestamps = false;

}
