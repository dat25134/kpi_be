<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function modulePermission()
    {
        return $this->belongsTo(\App\Models\ModulePermission::class, 'module_permission_id');
    }
} 