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
            'name' => $this->name,
            'description' => $this->description,
            'max_score' => $this->max_score,
            'weight' => $this->weight,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
} 