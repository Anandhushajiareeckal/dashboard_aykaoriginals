<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class BrandController extends Controller {
    public function index(Request $req) {
        $q = Brand::withCount('projects');
        if ($req->name) $q->where('name', 'like', '%'.$req->name.'%');
        return view('brands.index', ['brands' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view('brands.create'); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'location' => 'nullable',
            'contact_name' => 'nullable', 'contact_designation' => 'nullable',
            'email' => 'nullable|email', 'phone' => 'nullable',
            'website' => 'nullable|url', 'description' => 'nullable',
            'next_followup_date' => 'nullable|date',
        ]);
        $brand = Brand::create($data);
        $logger->created('Brand', $brand->id, $brand->name);
        return redirect()->route('brands.index')->with('success', 'Brand added.');
    }
    public function show(Brand $brand) {
        $brand->load('projects', 'followups.user', 'invoices', 'meetings');
        return view('brands.show', compact('brand'));
    }
    public function edit(Brand $brand) { return view('brands.edit', compact('brand')); }
    public function update(Request $req, Brand $brand, ActivityLogger $logger) {
        $data = $req->validate([
            'name' => 'required', 'location' => 'nullable',
            'contact_name' => 'nullable', 'contact_designation' => 'nullable',
            'email' => 'nullable|email', 'phone' => 'nullable',
            'website' => 'nullable|url', 'description' => 'nullable',
            'next_followup_date' => 'nullable|date',
        ]);
        $brand->update($data);
        $logger->updated('Brand', $brand->id, $brand->name);
        return redirect()->route('brands.show', $brand)->with('success', 'Brand updated.');
    }
    public function destroy(Brand $brand, ActivityLogger $logger) {
        $logger->deleted('Brand', $brand->id, $brand->name);
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand removed.');
    }
    public function addFollowup(Request $req, Brand $brand, ActivityLogger $logger) {
        $req->validate(['note' => 'required']);
        $brand->followups()->create(['user_id' => auth()->id(), 'note' => $req->note, 'followup_date' => $req->followup_date]);
        $logger->log('created', 'Follow-up', $brand->id, $brand->name, "Added follow-up for brand \"{$brand->name}\"");
        return back()->with('success', 'Follow-up logged.');
    }
}
