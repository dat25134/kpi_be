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
        $tasks = $this->taskRepository->getTasksWithFilters($request->all());

        // Lấy danh sách phòng ban có trong tasks của user hiện tại
        $departments = $this->taskRepository->getUserTaskDepartments();
        
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