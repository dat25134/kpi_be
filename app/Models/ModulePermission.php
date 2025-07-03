<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ModulePermission extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'display_name',
        'icon',
        'color',
        'description',
    ];

    protected static $logAttributes = [
        'name',
        'display_name',
        'icon',
        'color',
        'description',
    ];

    public function permissions()
    {
        return $this->hasMany(\Spatie\Permission\Models\Permission::class, 'module_permission_id');
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }
} 