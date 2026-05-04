<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return ProjectResource::collection($projects);
    }

    public function show(Project $project)
    {
        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($request->only(['status', 'name', 'budget_hours', 'end_date', 'consumed_hours']));
        return new ProjectResource($project->fresh());
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }
}
