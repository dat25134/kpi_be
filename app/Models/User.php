<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Task;
use App\Models\Evaluation;
use App\Models\Department;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'department_id',
        'status',
        'join_date',
        'password',
        'cccd',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        'join_date' => 'date',
        ];

    protected static $logAttributes = [
        'employee_id',
        'name',
        'email',
        'phone',
        'department_id',
        'status',
        'join_date',
        'password',
        'cccd',
    ];

    /**
     * Get the user's department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user's detailed information
     */
    public function info()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Get the tasks created by the user
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get the tasks assigned to the user (main assignee)
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'main_assignee_id');
    }

    /**
     * Get the tasks assigned by the user (assigner)
     */
    public function assignedByTasks()
    {
        return $this->hasMany(Task::class, 'assigner_id');
    }

    /**
     * Get the tasks where user is a collaborator
     */
    public function collaboratedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_collaborators', 'user_id', 'task_id');
    }

    /**
     * Get the evaluations of the user
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Get the department managed by the user
     */
    public function managedDepartment()
    {
        return $this->hasOne(Department::class, 'manager_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }
}
