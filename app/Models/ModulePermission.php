<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $fillable = [
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
} 