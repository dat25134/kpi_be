<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationCriteria extends Model
{
    use SoftDeletes;
    protected $table = 'evaluation_criteria';
    protected $fillable = [
        'role_id',
        'category_criteria_id',
        'name',
        'description',
        'max_score',
        'weight',
        'order',
        'is_active'
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function evaluationDetails()
    {
        return $this->hasMany(EvaluationDetail::class, 'criteria_id');
    }

    public function categoryCriteria()
    {
        return $this->belongsTo(CategoryCriteria::class, 'category_criteria_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRoleType($query, $roleType)
    {
        return $query->where('role_type', $roleType);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }
} 