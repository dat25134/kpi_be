<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

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
        'position',
        'status',
        'join_date',
        'password',
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

    // /**
    //  * Get the tasks created by the user
    //  */
    // public function createdTasks()
    // {
    //     return $this->hasMany(Task::class, 'created_by');
    // }

    // /**
    //  * Get the tasks assigned to the user
    //  */
    // public function assignedTasks()
    // {
    //     return $this->hasMany(Task::class, 'assigned_to');
    // }

    // /**
    //  * Get the evaluations of the user
    //  */
    // public function evaluations()
    // {
    //     return $this->hasMany(Evaluation::class);
    // }

    // /**
    //  * Get the department managed by the user
    //  */
    // public function managedDepartment()
    // {
    //     return $this->hasOne(Department::class, 'manager_id');
    // }

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role', 'status')
            ->withTimestamps();
    }
}
