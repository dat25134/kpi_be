<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatarUrl' => $this->info?->avatar ? asset('storage/' . $this->info->avatar) : null,
            'role' => $this->getRoleNames()->first(),
            'department' => $this->department?->name,
        ];
    }
} 