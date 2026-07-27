<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        return TaskResource::collection(
            $this->taskService->list($request->user())
        );
    }

    public function store(Request $request)
    {
        $task = $this->taskService->store($request, $request->user());

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($this->taskService->show($task));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($request, $task);

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskService->destroy($task);

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
