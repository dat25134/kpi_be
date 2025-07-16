<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\TaskRepository;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends Controller
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->can('project.view_all')) {
            // Lấy tất cả task và tất cả phòng ban
            $tasks = $this->taskRepository->getAllTasks($request->all());
            $departments = $this->taskRepository->getAllDepartments();
        } elseif ($user->can('project.view_related')) {
            // Lấy task và phòng ban liên quan đến user
            $tasks = $this->taskRepository->getRelatedTasks($user, $request->all());
            $departments = $this->taskRepository->getUserTaskDepartments($user);
        } else {
            // Chỉ lấy task do user được giao (hoặc không cho xem)
            $tasks = $this->taskRepository->getOwnTasks($user, $request->all());
            $departments = $this->taskRepository->getUserTaskDepartments($user);
        }
        return response()->json([
            'success' => true, 
            'message' => 'Lấy danh sách công việc thành công',
            'data' => [
                'tasks' => TaskResource::collection($tasks),
                'pagination' => [
                    'currentPage' => $tasks->currentPage(),
                    'totalPages' => $tasks->lastPage(),
                    'totalItems' => $tasks->total(),
                    'itemsPerPage' => $tasks->perPage(),
                ],
                'departments' => $departments,
            ],
        ]);
    }

    // public function show($id)
    // {
    //     $task = Task::with(['category', 'assigner', 'mainAssignee', 'collaborators'])->findOrFail($id);
    //     return new TaskResource($task);
    // }

    public function store(TaskStoreRequest $request)
    {
        $task = $this->taskRepository->createTask($request->all());
        if ($task) {
            return response()->json([
                'status' => true,
                'message' => 'Tạo task thành công',
                'data' => new TaskResource($task),
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Tạo task thất bại',
            ], 500);
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = $this->taskRepository->updateTask($id, $request->all());
        if ($task) {
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật task thành công',
                'data' => new TaskResource($task),
            ]);
        }
    }

    // public function destroy($id)
    // {
    //     $task = Task::findOrFail($id);
    //     $task->collaborators()->detach();
    //     $task->delete();
    //     return response()->json(['success' => true, 'message' => 'Xóa task thành công']);
    // }

    public function deleteFile($taskId, $id)
    {
        $task = Task::findOrFail($taskId);
        $task->deleteMedia($id);
        return response()->json(['success' => true, 'message' => 'Xóa file thành công']);
    }
} 