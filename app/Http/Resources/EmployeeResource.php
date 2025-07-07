<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EmployeeResource extends JsonResource
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
            'phone' => $this->phone,
            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ] : [
                'id' => null,
                'name' => 'Chưa có phòng ban',
                'code' => 'N/A'
            ],
            'cccd' => $this->cccd,
            'status' => $this->status,
            'joinDate' => $this->join_date ?? null,
            'salary' => $this->info?->salary !== null ? round($this->info->salary) : null,
            'address' => $this->info?->address,
            'birthDate' => $this->info?->birth_date ?? null,
            'gender' => $this->info?->gender,
            'education' => $this->info?->education,
            'experience' => $this->info?->experience,
            'skills' => $this->info?->skills,
            'projects' => $this->whenLoaded('projects', function () {
                return $this->projects->map(function ($project) {
                    return [
                        'name' => $project->name,
                        'role' => $project->pivot->role,
                        'status' => $project->pivot->status,
                    ];
                });
            }),
            'role' => [
                'name' => $this->getRoleNames()->first(),
                'displayName' => optional($this->roles->first())->display_name,
                'color' => optional($this->roles->first())->color,
            ],
            'permissions' => $this->getAllPermissions()->pluck('id'),
            'employee_id' => $this->employee_id,
        ];
    }

    private function getPositionName($position)
    {
        // Đã xóa function này vì không còn dùng
    }
} 