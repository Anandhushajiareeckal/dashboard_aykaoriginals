<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class CrewController extends Controller {
    public function index(Request $req) {
        $q = Crew::query();
        if ($req->name)   $q->where('name', 'like', '%'.$req->name.'%');
        if ($req->role)   $q->where('role', 'like', '%'.$req->role.'%');
        if ($req->status) $q->where('status', $req->status);
        return view('crew.index', ['crew' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view('crew.create'); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'role' => 'required', 'email' => 'nullable|email',
            'phone' => 'nullable', 'location' => 'nullable', 'status' => 'in:Available,On Project,Inactive',
        ]);
        $crew = Crew::create($data);
        $logger->created('Crew', $crew->id, $crew->name);
        return redirect()->route('crew.index')->with('success', 'Crew member added.');
    }
    public function show(Crew $crew) { $crew->load('projects'); return view('crew.show', compact('crew')); }
    public function edit(Crew $crew) { return view('crew.edit', compact('crew')); }
    public function update(Request $req, Crew $crew, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'role' => 'required', 'email' => 'nullable|email',
            'phone' => 'nullable', 'location' => 'nullable', 'status' => 'in:Available,On Project,Inactive',
        ]);
        $crew->update($data);
        $logger->updated('Crew', $crew->id, $crew->name);
        return redirect()->route('crew.index')->with('success', 'Crew member updated.');
    }
    public function destroy(Crew $crew, ActivityLogger $logger) {
        $logger->deleted('Crew', $crew->id, $crew->name);
        $crew->delete();
        return redirect()->route('crew.index')->with('success', 'Crew member removed.');
    }
}
