<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Meeting, Brand, Project, Employee};
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class MeetingController extends Controller {
    public function index() {
        $meetings = Meeting::with('brand', 'project', 'employee')->orderBy('meeting_at')->paginate(20);
        $upcoming = Meeting::with('brand')->where('meeting_at', '>', now())->orderBy('meeting_at')->take(10)->get();
        return view('meetings.index', compact('meetings', 'upcoming'));
    }
    public function create() {
        return view('meetings.create', [
            'brands'    => Brand::orderBy('name')->get(),
            'projects'  => Project::orderBy('title')->get(),
            'employees' => Employee::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'title' => 'required', 'brand_id' => 'nullable|exists:brands,id',
            'project_id' => 'nullable|exists:projects,id', 'employee_id' => 'nullable|exists:employees,id',
            'meeting_at' => 'required|date', 'duration_minutes' => 'integer|min:5',
            'mode' => 'in:Online,In-person,Hybrid', 'notes' => 'nullable',
        ]);
        $meeting = Meeting::create($data);
        $logger->created('Meeting', $meeting->id, $meeting->title);
        return redirect()->route('meetings.index')->with('success', 'Meeting scheduled.');
    }
    public function destroy(Meeting $meeting, ActivityLogger $logger) {
        $logger->deleted('Meeting', $meeting->id, $meeting->title);
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Meeting removed.');
    }
}
