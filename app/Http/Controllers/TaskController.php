<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\TaskRepository;

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
            ],
        ]);
    }

    // public function show($id)
    // {
    //     $task = Task::with(['category', 'assigner', 'mainAssignee', 'collaborators'])->findOrFail($id);
    //     return new TaskResource($task);
    // }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'content' => 'required|string',
    //         'start_date' => 'required|date',
    //         'due_date' => 'required|date',
    //         'category_id' => 'required|exists:categories,id',
    //         'weight' => 'nullable|integer|min:1|max:10',
    //         'assigner_id' => 'required|exists:users,id',
    //         'main_assignee_id' => 'required|exists:users,id',
    //         'status' => 'nullable|in:pending,in_progress,completed,cancelled',
    //         'created_by' => 'required|exists:users,id',
    //         'collaborators' => 'nullable|array',
    //         'collaborators.*' => 'exists:users,id',
    //     ]);
    //     DB::beginTransaction();
    //     try {
    //         $task = Task::create($data);
    //         if (!empty($data['collaborators'])) {
    //             $task->collaborators()->sync($data['collaborators']);
    //         }
    //         DB::commit();
    //         return new TaskResource($task->load(['category', 'assigner', 'mainAssignee', 'collaborators']));
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'Lỗi tạo task', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // public function update(Request $request, $id)
    // {
    //     $task = Task::findOrFail($id);
    //     $data = $request->validate([
    //         'content' => 'required|string',
    //         'start_date' => 'required|date',
    //         'due_date' => 'required|date',
    //         'category_id' => 'required|exists:categories,id',
    //         'weight' => 'nullable|integer|min:1|max:10',
    //         'assigner_id' => 'required|exists:users,id',
    //         'main_assignee_id' => 'required|exists:users,id',
    //         'status' => 'nullable|in:pending,in_progress,completed,cancelled',
    //         'created_by' => 'required|exists:users,id',
    //         'collaborators' => 'nullable|array',
    //         'collaborators.*' => 'exists:users,id',
    //     ]);
    //     DB::beginTransaction();
    //     try {
    //         $task->update($data);
    //         if (isset($data['collaborators'])) {
    //             $task->collaborators()->sync($data['collaborators']);
    //         }
    //         DB::commit();
    //         return new TaskResource($task->load(['category', 'assigner', 'mainAssignee', 'collaborators']));
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'Lỗi cập nhật task', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // public function destroy($id)
    // {
    //     $task = Task::findOrFail($id);
    //     $task->collaborators()->detach();
    //     $task->delete();
    //     return response()->json(['success' => true, 'message' => 'Xóa task thành công']);
    // }
} 