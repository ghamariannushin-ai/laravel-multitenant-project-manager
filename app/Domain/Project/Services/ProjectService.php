<?php

namespace App\Domain\Project\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domain\Project\Models\Project;

class ProjectService
{
    public function list(User $user)
    {
        return Project::where('user_id', $user->id)->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', 'max:50'],
        ]);

        $validated['user_id'] = Auth::id();

        return Project::create($validated);
    }

    public function show(Project $project)
    {
        return $project;
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', 'max:50'],
        ]);

        $project->update($validated);

        return $project;
    }

    public function destroy(Project $project)
    {
        $project->delete();
    }
}
