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
        $projects = Project::where('published_at', '<=', now())
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
        if ($project->published_at > now() || $project->published_at === null) {
            return redirect()->route('projects.index');
        }

        Head::title($project->title)
            ->description($project->intro);

        return view('public.projects.show', [
            'project' => $project,
        ]);
    }
}
