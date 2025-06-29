<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'displayName' => $this->display_name,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status,
        ];
    }
} 