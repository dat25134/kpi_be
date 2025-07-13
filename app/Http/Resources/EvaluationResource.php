<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        $user = $this->user;
        $role = $user->roles->first();
        $data = [
            'id' => $this->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'department' => $user->department->name ?? null,
                'role' => $role->name,
                'roleName' => $role->display_name,
            ],
            'month' => $this->month,
            'year' => $this->year,
            'status' => $this->status,
            'final_grade' => $this->final_grade,
            'total_score' => $this->total_score,
            'department' => $this->department,
        ];
        // Nếu là show (single resource), trả về chi tiết
        if ($this->resource instanceof \App\Models\Evaluation && $this->relationLoaded('evaluationDetails')) {
            $data['details'] = EvaluationDetailResource::collection($this->evaluationDetails)->sortBy('criteria.order')->values();
        }
        if ($this->resource instanceof \App\Models\Evaluation && $this->relationLoaded('workDescriptions')) {
            $data['work_descriptions'] = WorkDescriptionResource::collection($this->workDescriptions);
        }
        return $data;
    }
} 