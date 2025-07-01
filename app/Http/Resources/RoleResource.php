<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'status' => $this->status,
            'order' => $this->order,
            'color' => $this->color,
            'permissions' => $this->permissions ? $this->permissions->pluck('id')->toArray() : [],
            'employee_count' => $this->users ? $this->users->count() : 0,
            'employees' => $this->users ? $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ];
            }) : [],
        ];
    }
} 