<?php
namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id"               => $this->id,
            "content"          => $this->content,
            "status"           => $this->status,
            "category"         => $this->category,
            "assignees"        => $this->collaborators->map(function ($collaborator) {
                return [
                    "id" => $collaborator->id,
                    "name" => $collaborator->name,
                ];
            }),
            "count"            => $this->weight,
            "startDate"        => Carbon::parse($this->start_date)->format('d/m/Y'),
            "deadline"         => Carbon::parse($this->due_date)->format('d/m/Y'),
            "createdAt"        => Carbon::parse($this->created_at)->format('d/m/Y'),
            "assigner"         => $this->assigner_id,
            "assigner_name"    => $this->assigner->name,
            "mainHandler"      => $this->mainAssignee,
            "description"      => $this->description,
        ];
    }
}
