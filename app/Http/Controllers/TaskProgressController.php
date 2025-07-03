<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskProgressResource;
use App\Http\Requests\TaskProgress\TaskProgressStoreRequest;
use App\Repositories\Contracts\TaskProgressRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TaskProgressController extends Controller
{
    protected $taskProgressRepository;

    public function __construct(TaskProgressRepositoryInterface $taskProgressRepository)
    {
        $this->taskProgressRepository = $taskProgressRepository;
    }

    public function store(TaskProgressStoreRequest $request, $taskId)
    {
        $progress = $this->taskProgressRepository->createProgress([
            'task_id' => $taskId,
            'user_id' => Auth::user()->id,
            'content' => $request->content,
        ]);
        $progress->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Thêm tiến độ công việc thành công',
            'data' => new TaskProgressResource($progress)
        ]);
    }
} 