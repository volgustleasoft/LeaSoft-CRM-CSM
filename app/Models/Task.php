<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'Task';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['JobId', 'SubmissionHref', 'State', 'DateTimeCreated','DateTimePosted',"DateTimeFetched","DateTimeCompleted","PostError","DateTimeProcessed"];


}
