<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
    ];

    protected static $logAttributes = [
        'name',
        'display_name',
        'description',
        'color',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }
} 