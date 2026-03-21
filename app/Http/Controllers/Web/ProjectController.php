<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Project, Brand, TalentModel, Crew};
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class ProjectController extends Controller {
    public function index(Request $req) {
        $q = Project::with('brand');
        if ($req->status)   $q->where('status', $req->status);
        if ($req->brand_id) $q->where('brand_id', $req->brand_id);
        return view('projects.index', [
            'projects' => $q->latest()->paginate(15)->withQueryString(),
            'brands'   => Brand::orderBy('name')->get(),
        ]);
    }
    public function create() {
        return view('projects.create', [
            'brands' => Brand::orderBy('name')->get(),
            'models' => TalentModel::where('status', 'Active')->orderBy('name')->get(),
            'crew'   => Crew::orderBy('name')->get(),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'title' => 'required', 'brand_id' => 'nullable|exists:brands,id',
            'category' => 'nullable', 'budget' => 'nullable|numeric',
            'start_date' => 'nullable|date', 'end_date' => 'nullable|date',
            'status' => 'in:Planning,Active,Review,Completed,Cancelled', 'notes' => 'nullable',
        ]);
        $project = Project::create($data);
        if ($req->model_ids) $project->models()->sync($req->model_ids);
        if ($req->crew_ids)  $project->crew()->sync($req->crew_ids);
        $logger->created('Project', $project->id, $project->title);
        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }
    public function show(Project $project) {
        $project->load('brand', 'models', 'crew', 'invoices', 'meetings');
        return view('projects.show', compact('project'));
    }
    public function edit(Project $project) {
        return view('projects.edit', [
            'project' => $project, 'brands' => Brand::orderBy('name')->get(),
            'models' => TalentModel::orderBy('name')->get(), 'crew' => Crew::orderBy('name')->get(),
        ]);
    }
    public function update(Request $req, Project $project, ActivityLogger $logger) {
        $data = $req->validate([
            'title' => 'required', 'brand_id' => 'nullable|exists:brands,id',
            'category' => 'nullable', 'budget' => 'nullable|numeric',
            'start_date' => 'nullable|date', 'end_date' => 'nullable|date',
            'status' => 'in:Planning,Active,Review,Completed,Cancelled',
            'progress' => 'integer|min:0|max:100', 'notes' => 'nullable',
        ]);
        $project->update($data);
        if ($req->has('model_ids')) $project->models()->sync($req->model_ids ?? []);
        if ($req->has('crew_ids'))  $project->crew()->sync($req->crew_ids ?? []);
        $logger->updated('Project', $project->id, $project->title);
        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }
    public function destroy(Project $project, ActivityLogger $logger) {
        $logger->deleted('Project', $project->id, $project->title);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
