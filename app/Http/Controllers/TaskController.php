<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\TaskRepository;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function store(TaskStoreRequest $request)
    {
        $user = $request->user();
        $isSubTask = $request->filled('parent_id') && $request->parent_id !== null;

        if ($isSubTask) {
            if (!$user->can('project.create_sub')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không có quyền tạo sub-task!'
                ], 403);
            }
        } else {
            if (!$user->can('project.create')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không có quyền tạo task!'
                ], 403);
            }
        }

        $task = $this->taskRepository->createTask($request->all());
        if ($task) {
            return response()->json([
                'status' => true,
                'message' => 'Tạo task thành công',
                'data' => new TaskResource($task),
            ]);
        } else {
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

    /**
     * Export danh sách công việc ra file Word theo template
     */
    public function exportWord(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);
        $ids = $request->input('ids');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $user = $request->user();
        Log::info($ids);
        // Lấy danh sách task
        $tasks = \App\Models\Task::with(['department'])
            ->whereIn('id', $ids)
            ->get();
        if ($tasks->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy công việc nào.'], 404);
        }

        // Phân loại task
        $departmentName = $user->department->name ?? 'Chưa có phòng ban';
        $tasksCompletedType1 = [];
        $tasksCompletedType2 = [];
        $tasksInProgressType1 = [];
        $tasksInProgressType2 = [];
        $space = str_repeat("\u{00A0}", 12);
        foreach ($tasks as $task) {
            $isInUserDept = $task->category_id == 1;
            $status = $task->status ?? 'in_progress';
            $taskLine = $space . "- " . $task->content;
            if ($status === 'completed') {
                if ($isInUserDept) {
                    $tasksCompletedType1[] = $taskLine;
                } else {
                    $tasksCompletedType2[] = $taskLine;
                }
            } else {
                if ($isInUserDept) {
                    $tasksInProgressType1[] = $taskLine;
                } else {
                    $tasksInProgressType2[] = $taskLine;
                }
            }
        }

        // Chuẩn bị dữ liệu cho template
        $now = Carbon::now();
        $templatePath = resource_path('template/template_export.docx');
        $templateProcessor = new TemplateProcessor($templatePath);
        $templateProcessor->setValue('startDate', Carbon::parse($startDate)->format('d/m/Y'));
        $templateProcessor->setValue('endDate', Carbon::parse($endDate)->format('d/m/Y'));
        $templateProcessor->setValue('day', $now->day);
        $templateProcessor->setValue('month', $now->month);
        $templateProcessor->setValue('year', $now->year);
        $templateProcessor->setValue('departmentName', $departmentName);
        $templateProcessor->setValue('listTaskNameCompletedType1', count($tasksCompletedType1) ? implode("\n", $tasksCompletedType1) : $space .'Không có công việc nào được ghi nhận.');
        $templateProcessor->setValue('listTaskNameCompletedType2', count($tasksCompletedType2) ? implode("\n", $tasksCompletedType2) : $space .'Không có công việc nào được ghi nhận.');
        $templateProcessor->setValue('listTaskNameInProgressType1', count($tasksInProgressType1) ? implode("\n", $tasksInProgressType1) : $space .'Không có công việc nào được ghi nhận.');
        $templateProcessor->setValue('listTaskNameInProgressType2', count($tasksInProgressType2) ? implode("\n", $tasksInProgressType2) : $space .'Không có công việc nào được ghi nhận.');

        // Lưu file tạm
        $fileName = 'tasks_export_' . $startDate . '_' . $endDate . '.docx';
        $tempPath = storage_path('app/tmp/' . Str::random(16) . '_' . $fileName);
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        $templateProcessor->saveAs($tempPath);

        // Trả file về FE
        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
} 