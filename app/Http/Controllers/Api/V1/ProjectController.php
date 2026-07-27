<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Services\ProjectService;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service) {}

    public function index(Request $request)
    {
        $tenant = app('tenant');

        return ProjectResource::collection(
            $this->service->list($request->user())
        )->additional([
            'meta' => [
                'tenant' => $tenant?->domain
            ]
        ]);
    }

    public function store(Request $request)
    {
        $project = $this->service->store($request);

        return (new ProjectResource($project))
            ->additional([
                'meta' => [
                    'message' => 'Project created successfully'
                ]
            ]);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource(
            $this->service->show($project)
        );
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $project = $this->service->update($request, $project);

        return (new ProjectResource($project))
            ->additional([
                'meta' => [
                    'message' => 'Project updated successfully'
                ]
            ]);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->service->destroy($project);

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }
}
