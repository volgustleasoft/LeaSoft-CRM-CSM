<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentQuestionNotification extends Model
{
    use HasFactory;

    protected $table = 'UrgentQuestionNotification';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['QuestionId', 'PersonId', 'UniqueCode', 'RejectedAt', 'CreatedAt'];

    public function isExistsToken($token) {
        return self::query()
            ->select($this->table. '.Id',)
            ->where('UniqueCode', '=', $token)
            ->where('CreatedAt', '>', date("Y-m-d 00:00:00", strtotime("-1 year")))
            ->get()->first();
    }
}
