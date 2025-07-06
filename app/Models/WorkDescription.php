<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkDescription extends Model
{
    protected $fillable = [
        'evaluation_id',
        'task_id',
        'task_title',
        'task_description',
        'task_status',
        'task_start_date',
        'task_due_date',
        'task_weight',
        'unit',
        'target',
        'complexity_weight',
        'quality_weight',
        'result_level',
        'result_score',
        'final_score',
        'explanation',
        'order'
    ];

    protected $casts = [
        'task_start_date' => 'date',
        'task_due_date' => 'date',
        'task_weight' => 'integer',
        'complexity_weight' => 'integer',
        'quality_weight' => 'integer',
        'result_level' => 'integer',
        'result_score' => 'decimal:4',
        'final_score' => 'decimal:4'
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
} 