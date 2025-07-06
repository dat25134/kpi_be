<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkDescriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'task_status' => $this->task_status,
            'task_start_date' => $this->task_start_date,
            'task_due_date' => $this->task_due_date,
            'task_weight' => $this->task_weight,
            'task_title' => $this->task_title,
            'task_description' => $this->task_description,
            'unit' => $this->unit,
            'target' => $this->target,
            'complexity_weight' => $this->complexity_weight,
            'quality_weight' => $this->quality_weight,
            'result_level' => $this->result_level,
            'result_score' => $this->result_score,
            'final_score' => $this->final_score,
            'explanation' => $this->explanation,
            'order' => $this->order,
        ];
    }
} 