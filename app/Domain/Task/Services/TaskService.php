<?php

namespace App\Domain\Task\Services;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;


class TaskService
{
public function list(User $user)
{
    return Task::whereHas('project', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->latest()
        ->get();
}



public function store(Request $request, User $user): Task
{
    $validated = $request->validate([
        'project_id' => 'required|exists:tenant.projects,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'nullable|in:low,medium,high',
        'due_date' => 'nullable|date',
    ]);

    $project = Project::where('id', $validated['project_id'])
        ->where('user_id', $user->id)
        ->first();

    if (! $project) {
        throw new AuthorizationException('You are not allowed to create task for this project.');
    }

    $validated['status'] = 'todo';

    $task = Task::create($validated);

    return $task->refresh();
}





    public function show(Task $task)
    {
        return $task;
    }

    public function update(Request $request, Task $task): Task
{
    $validated = $request->validate([
        'title' => ['sometimes', 'string', 'max:255'],
        'status' => ['sometimes', 'nullable', 'in:todo,in_progress,done'],
        'is_completed' => ['sometimes', 'boolean'],
        'description' => ['sometimes', 'nullable', 'string'],
        'priority' => ['sometimes', 'nullable', 'string'],
        'due_date' => ['sometimes', 'nullable', 'date'],
    ]);

    $task->update($validated);

    return $task->refresh();
}


    public function destroy(Task $task)
    {
        $task->delete();
    }
}
