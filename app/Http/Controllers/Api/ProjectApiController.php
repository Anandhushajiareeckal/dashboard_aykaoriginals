<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectApiController extends Controller {
    public function index(Request $req) {
        $q = Project::with('brand');
        if ($req->status) $q->where('status',$req->status);
        return response()->json($q->latest()->paginate(20));
    }
    public function show(Project $project) {
        $project->load('brand','models','crew','invoices');
        return response()->json($project);
    }
}
