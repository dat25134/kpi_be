<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'criteria' => [
                'id' => $this->criteria->id,
                'name' => $this->criteria->name,
                'category' => $this->criteria->categoryCriteria->name,
                'max_score' => $this->criteria->max_score,
            ],
            'self_score' => $this->self_score,
            'self_comment' => $this->self_comment,
            'level1_score' => $this->level1_score,
            'level1_comment' => $this->level1_comment,
            'level2_score' => $this->level2_score,
            'level2_comment' => $this->level2_comment,
            'final_score' => $this->final_score,
        ];
    }
} 