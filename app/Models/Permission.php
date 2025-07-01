<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function modulePermission()
    {
        return $this->belongsTo(ModulePermission::class, 'module_permission_id');
    }
} 