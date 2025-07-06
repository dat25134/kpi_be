<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationDetail extends Model
{
    protected $fillable = [
        'evaluation_id',
        'criteria_id',
        'self_score',
        'self_comment',
        'level1_score',
        'level1_comment',
        'level2_score',
        'level2_comment',
        'final_score'
    ];

    protected $casts = [
        'self_score' => 'decimal:2',
        'level1_score' => 'decimal:2',
        'level2_score' => 'decimal:2',
        'final_score' => 'decimal:2'
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criteria()
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }
} 