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
            "startDate"        => $this->start_date,
            "deadline"         => $this->due_date,
            "createdAt"        => $this->created_at,
            "assigner"         => $this->assigner,
            "mainHandler"      => $this->mainAssignee,
            "description"      => $this->description,
        ];
    }
}
