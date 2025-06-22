<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        $manager = $this->manager;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'manager' => $manager ? [
                'id' => $manager->id,
                'name' => $manager->name,
                'avatar' => $manager->name ? collect(explode(' ', $manager->name))->map(fn($w) => mb_substr($w, 0, 1))->join('') : null,
                'position' => $this->getPositionName($manager->position),
            ] : null,
            'employee_count' => $this->employees()->count(),
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }

    private function getPositionName($position)
    {
        return match ($position) {
            'employee'   => 'Nhân viên',
            'specialist' => 'Chuyên viên',
            'manager'    => 'Phó phòng',
            'director'   => 'Trưởng phòng',
            default      => $position,
        };
    }
} 