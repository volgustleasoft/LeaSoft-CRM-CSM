<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAvailableTimeSlot extends Model
{
    use HasFactory;

    protected $table = 'QuestionAvailableTimeSlot';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['QuestionId', 'DateTimeFrom', 'DateTimeTo'];

    public function Question()
    {
        return $this->hasOne(Question::class, "Id", "QuestionId");
    }
}
