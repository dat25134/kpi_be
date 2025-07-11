<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationCriteriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'category_criteria_id' => $this->category_criteria_id,
            'name' => $this->name,
            'description' => $this->description,
            'max_score' => $this->max_score,
            'weight' => $this->weight,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'role' => $this->whenLoaded('role'),
            'category_criteria' => $this->whenLoaded('categoryCriteria'),
        ];
    }
} 