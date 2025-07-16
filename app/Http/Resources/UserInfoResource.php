<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource
{
    public function toArray($request)
    {
        $avatarUrl = $this->info?->avatar ? asset('storage/' . $this->info->avatar) : null;
        $avatar = $this->name ? collect(explode(' ', $this->name))->map(fn($w) => mb_substr($w, 0, 1))->join('') : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $avatar,
            'avatarUrl' => $avatarUrl,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ] : null,
        ];
    }
} 