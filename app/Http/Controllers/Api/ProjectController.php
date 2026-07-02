<?php

namespace App\Http\Controllers\Api;

use App\Domain\Project\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index()
{
    $tenant = app('tenant');

    return response()->json([
        'tenant' => $tenant?->domain,
        'projects' => Project::all(),
    ]);
}

public function store(Request $request)
{
    $tenant = app('tenant');

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
    ]);

    $project = Project::create($validated);

    return response()->json([
        'message' => 'Project created successfully',
        'tenant' => $tenant?->domain,
        'project' => $project,
    ], 201);
}



    public function update(Request $request, $id)
    {
    $project = Project::findOrFail($id);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
    ]);

    $project->update($validated);

    return response()->json([
        'message' => 'Project updated successfully',
        'project' => $project,
    ]);
    }


    public function destroy($id)
    {
    $project = Project::findOrFail($id);

    $project->delete();

    return response()->json([
        'message' => 'Project deleted successfully'
    ]);
    }

}
