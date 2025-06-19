<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInfo extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Get the user that owns the info.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 