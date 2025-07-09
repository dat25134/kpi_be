<?php
namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        $currentUserId = Auth::id();
        $userRole = $this->getUserRoleInTask($currentUserId);
        
        return [
            "id"               => $this->id,
            "content"          => $this->content,
            "status"           => $this->status,
            "category"         => $this->category,
            "department"       => $this->department ? [
                "id" => $this->department->id,
                "name" => $this->department->name,
                "code" => $this->department->code,
            ] : null,
            "userRole"         => $userRole,
            "assignees"        => $this->collaborators->map(function ($collaborator) {
                return [
                    "id" => $collaborator->id,
                    "name" => $collaborator->name,
                ];
            }),
            "count"            => $this->weight,
            "qualityWeight"    => $this->quality_weight,
            "startDate"        => $this->start_date,
            "deadline"         => $this->due_date,
            "createdAt"        => $this->created_at,
            "assigner"         => $this->assigner,
            "mainHandler"      => $this->mainAssignee,
            "description"      => $this->description,
            "progressHistory"  => TaskProgressResource::collection($this->whenLoaded('progressHistory')),
            "files" => $this->getMedia('task_files')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                ];
            }),
            "subtasks" => $this->subtasks->map(function ($subtask) {
                return [
                    'id' => $subtask->id,
                    'status' => $subtask->status,
                    'content' => $subtask->content,
                    'startDate' => $subtask->start_date,
                    'deadline' => $subtask->due_date,
                    'mainHandler' => $subtask->mainAssignee,
                    'assignees' => $subtask->collaborators->map(function ($collaborator) {
                        return [
                            'id' => $collaborator->id,
                            'name' => $collaborator->name,
                        ];
                    }),
                    'count' => $subtask->weight,
                    'qualityWeight' => $subtask->quality_weight,
                    'createdAt' => $subtask->created_at,
                ];
            }),
        ];
    }

    /**
     * Xác định vai trò của user hiện tại trong task
     */
    private function getUserRoleInTask($userId)
    {
        if ($this->main_assignee_id == $userId) {
            return 'main_assignee';
        }
        
        if ($this->assigner_id == $userId) {
            return 'assigner';
        }
        
        if ($this->created_by == $userId) {
            return 'creator';
        }
        
        if ($this->collaborators->contains('id', $userId)) {
            return 'collaborator';
        }
        
        return null;
    }
}
