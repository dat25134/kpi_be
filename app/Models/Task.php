<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use LogsActivity;

    protected $fillable = [
        'content',
        'start_date',
        'due_date',
        'category_id',
        'department_id',
        'weight',
        'assigner_id',
        'main_assignee_id',
        'status',
        'created_by',
    ];

    protected static $logAttributes = [
        'content',
        'start_date',
        'due_date',
        'category_id',
        'department_id',
        'weight',
        'assigner_id',
        'main_assignee_id',
        'status',
        'created_by',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
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