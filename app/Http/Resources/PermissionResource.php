<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'module' => $this->modulePermission->name ?? null,
            'category' => $this->category,
            'description' => $this->description,
        ];
    }
} 