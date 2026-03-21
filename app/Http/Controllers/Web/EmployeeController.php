<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class EmployeeController extends Controller {
    public function index(Request $req) {
        $q = Employee::query();
        if ($req->name)       $q->where('name', 'like', '%'.$req->name.'%');
        if ($req->department) $q->where('department', $req->department);
        if ($req->status)     $q->where('status', $req->status);
        return view('employees.index', ['employees' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view('employees.create'); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'department' => 'nullable', 'email' => 'nullable|email',
            'phone' => 'nullable', 'salary' => 'nullable|numeric',
            'joining_date' => 'nullable|date', 'status' => 'in:Active,On Leave,Resigned',
        ]);
        $emp = Employee::create($data);
        $logger->created('Employee', $emp->id, $emp->name);
        return redirect()->route('employees.index')->with('success', 'Employee added.');
    }
    public function show(Employee $employee) { $employee->load('user', 'meetings'); return view('employees.show', compact('employee')); }
    public function edit(Employee $employee) { return view('employees.edit', compact('employee')); }
    public function update(Request $req, Employee $employee, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'department' => 'nullable', 'email' => 'nullable|email',
            'phone' => 'nullable', 'salary' => 'nullable|numeric',
            'joining_date' => 'nullable|date', 'status' => 'in:Active,On Leave,Resigned',
        ]);
        $employee->update($data);
        $logger->updated('Employee', $employee->id, $employee->name);
        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }
    public function destroy(Employee $employee, ActivityLogger $logger) {
        $logger->deleted('Employee', $employee->id, $employee->name);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee removed.');
    }
}
