<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department',
        'month',
        'year',
        'total_score',
        'final_grade',
        'status'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_score' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function evaluationDetails()
    {
        return $this->hasMany(EvaluationDetail::class);
    }

    public function workDescriptions()
    {
        return $this->hasMany(WorkDescription::class);
    }

    public function approvals()
    {
        return $this->hasMany(EvaluationApproval::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMonthYear($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRoleType($query, $roleType)
    {
        return $query->whereHas('user.roles', function ($q) use ($roleType) {
            $q->where('name', $roleType);
        });
    }

    /**
     * Lấy role_type của user trong evaluation này
     */
    public function getRoleTypeAttribute()
    {
        $userRole = $this->user->roles->first();
        if (!$userRole) {
            return null;
        }

        // Map tên role sang role_type cho đánh giá KPI
        $roleMapping = [
            'truongphong' => 'truongphong',
            'phophong' => 'phophong',
            'nhanvien' => 'nhanvien',
        ];

        return $roleMapping[$userRole->name] ?? null;
    }

    public function calculateTotalScore()
    {
        $totalScore = $this->evaluationDetails->sum('final_score');
        $this->update(['total_score' => $totalScore]);
        return $totalScore;
    }

    public function calculateFinalGrade()
    {
        $score = $this->total_score;
        if ($score >= 90) {
            $grade = 'A';
        } elseif ($score >= 70) {
            $grade = 'B';
        } elseif ($score >= 50) {
            $grade = 'C';
        } else {
            $grade = 'D';
        }
        
        $this->update(['final_grade' => $grade]);
        return $grade;
    }
} 