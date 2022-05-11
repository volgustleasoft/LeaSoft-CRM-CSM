<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareGroupPerson extends Model
{
    use HasFactory;

    public function Person()
    {
        return $this->hasOne(Person::class, "Id", "PersonId");
    }

    protected $fillable = ['PersonId, CareGroupId'];
    protected $table = 'CareGroupPerson';
    protected $primaryKey = 'Id';
    public $timestamps = false;
}
