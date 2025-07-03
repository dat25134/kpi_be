<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'content',
        'start_date',
        'due_date',
        'category_id',
        'weight',
        'assigner_id',
        'main_assignee_id',
        'status',
        'created_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }

    public function mainAssignee()
    {
        return $this->belongsTo(User::class, 'main_assignee_id');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'task_collaborators', 'task_id', 'user_id');
    }

    public function progressHistory()
    {
        return $this->hasMany(TaskProgress::class)->orderBy('created_at', 'asc');
    }
} 