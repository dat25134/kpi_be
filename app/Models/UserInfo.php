<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class UserInfo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'user_info';

    protected $fillable = [
        'user_id',
        'birth_date',
        'avatar',
        'address',
        'education',
        'experience',
        'skills',
        'gender',
        'salary'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'skills' => 'array',
        'salary' => 'decimal:2'
    ];

    protected static $logAttributes = [
        'user_id',
        'birth_date',
        'avatar',
        'address',
        'education',
        'experience',
        'skills',
        'gender',
        'salary'
    ];

    /**
     * Get the user that owns the info.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }
} 