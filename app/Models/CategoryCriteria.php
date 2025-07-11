<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryCriteria extends Model
{
    protected $table = 'category_criteria';
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function evaluationCriterias()
    {
        return $this->hasMany(EvaluationCriteria::class, 'category_criteria_id');
    }
} 