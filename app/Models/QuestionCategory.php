<?php

namespace App\Models;

use App\Models\QuestionCategoryAllowedCareGroupLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    use HasFactory;

    protected $table = 'QuestionCategory';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    public function getChildrenQuestionCategories() {
        return QuestionCategory::query()
            ->select('QuestionCategory.*')
            ->where('ParentId', '=', $this->Id)
            ->get()->all();
    }

    public function getCareGroupLabels(){
        $result = array_column(QuestionCategoryAllowedCareGroupLabel::where('QuestionCategoryId', '=', $this->Id)->get()->all(),'CareGroupLabelId');
        return CareGroupLabel::whereIn('Id', $result)->get()->all();
    }

    public function getParentQuestionCategory() {
        return QuestionCategory::query()
            ->where('Id', "=", $this->ParentId)
            ->get()->first();
    }
}
