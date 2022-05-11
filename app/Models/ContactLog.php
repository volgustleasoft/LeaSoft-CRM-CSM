<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactLog extends Model
{
    use HasFactory;

    protected $table = 'ContactLog';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['PersonId', 'ContactType', 'Reference', 'DateTime'];
}
