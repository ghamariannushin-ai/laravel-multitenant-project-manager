<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Task\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        return response()->json([
            'tasks' => Task::all()
        ]);
    }

    public function store(Request $request)
{
    $tenant = app('tenant');

    $validated = $request->validate([
        'project_id' => 'required|exists:tenant.projects,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'nullable|string'
    ]);

    $task = Task::create($validated);

    return response()->json([
        'message' => 'Task created successfully',
        'tenant' => $tenant?->domain,
        'task' => $task
    ], 201);
}

    public function show($id)
    {
        return response()->json([
            'task' => Task::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string']
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
