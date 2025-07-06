<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationApproval extends Model
{
    protected $fillable = [
        'evaluation_id',
        'approver_id',
        'level',
        'action',
        'comment',
        'approved_at'
    ];

    protected $casts = [
        'level' => 'integer',
        'approved_at' => 'datetime'
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
} 