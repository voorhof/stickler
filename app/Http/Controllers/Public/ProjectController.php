<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Laravel\Head\Facades\Head;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::isPublicPublished()
            ->with('media')
            ->orderBy('order_column', 'asc')
            ->paginate(6);

        Head::title(__('Projects'))
            ->description(__('Projects').' '.config('app.name'));

        return view('public.projects.index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        abort_unless($project->isPublished(), 404);

        Head::title($project->title)
            ->description($project->intro);

        return view('public.projects.show', [
            'project' => $project,
        ]);
    }
}
