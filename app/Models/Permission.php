<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends SpatiePermission
{
    use LogsActivity;

    public function modulePermission()
    {
        return $this->belongsTo(ModulePermission::class, 'module_permission_id');
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()->logAll()->logOnlyDirty();
    }
} 