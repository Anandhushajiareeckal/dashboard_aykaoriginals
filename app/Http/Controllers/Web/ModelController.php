<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TalentModel;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index(Request $req)
    {
        $q = TalentModel::query();
        if ($req->name)       $q->where('name', 'like', '%'.$req->name.'%');
        if ($req->location)   $q->where('location', 'like', '%'.$req->location.'%');
        if ($req->status)     $q->where('status', $req->status);
        if ($req->inhouse)    $q->where('is_inhouse', true);
        if ($req->category)   $q->whereJsonContains('categories', $req->category);
        if ($req->height_min) $q->whereRaw('CAST(REGEXP_REPLACE(height, "[^0-9]", "") AS UNSIGNED) >= ?', [(int)$req->height_min]);
        if ($req->height_max) $q->whereRaw('CAST(REGEXP_REPLACE(height, "[^0-9]", "") AS UNSIGNED) <= ?', [(int)$req->height_max]);
        $models = $q->latest()->paginate(12)->withQueryString();
        return view('models.index', compact('models'));
    }

    public function create()
    {
        return view('models.create');
    }

    public function store(Request $req, ActivityLogger $logger)
    {
        $data = $req->validate([
            'name'      => 'required',
            'age'       => 'nullable|integer|min:16|max:80',
            'height'    => 'nullable|string|max:20',
            'bust'      => 'nullable|string|max:20',
            'waist'     => 'nullable|string|max:20',
            'hips'      => 'nullable|string|max:20',
            'shoe_size' => 'nullable|string|max:20',
            'categories' => 'nullable|array',
            'email'     => 'nullable|email',
            'phone'     => 'nullable',
            'location'  => 'nullable',
            'about'     => 'nullable',
            'budget'    => 'nullable|numeric',
            'is_inhouse' => 'boolean',
            'status'    => 'in:Active,Inactive,On Leave,Unavailable',
        ]);

        $data['is_inhouse'] = $req->boolean('is_inhouse');
        $model = TalentModel::create($data);

        if ($req->hasFile('images')) {
            foreach ($req->file('images') as $image) {
                $model->addMedia($image)->toMediaCollection('portfolio');
            }
        }

        $logger->created('Model', $model->id, $model->name);
        return redirect()->route('models.show', $model)->with('success', 'Model added successfully.');
    }

    public function show(TalentModel $model)
    {
        $model->load('projects');
        return view('models.show', compact('model'));
    }

    public function edit(TalentModel $model)
    {
        return view('models.edit', compact('model'));
    }

    public function update(Request $req, TalentModel $model, ActivityLogger $logger)
    {
        $data = $req->validate([
            'name'      => 'required',
            'age'       => 'nullable|integer|min:16|max:80',
            'height'    => 'nullable|string|max:20',
            'bust'      => 'nullable|string|max:20',
            'waist'     => 'nullable|string|max:20',
            'hips'      => 'nullable|string|max:20',
            'shoe_size' => 'nullable|string|max:20',
            'categories' => 'nullable|array',
            'email'     => 'nullable|email',
            'phone'     => 'nullable',
            'location'  => 'nullable',
            'about'     => 'nullable',
            'budget'    => 'nullable|numeric',
            'is_inhouse' => 'boolean',
            'status'    => 'in:Active,Inactive,On Leave,Unavailable',
        ]);

        $data['is_inhouse'] = $req->boolean('is_inhouse');
        $model->update($data);

        if ($req->hasFile('images')) {
            foreach ($req->file('images') as $image) {
                $model->addMedia($image)->toMediaCollection('portfolio');
            }
        }

        $logger->updated('Model', $model->id, $model->name);
        return redirect()->route('models.show', $model)->with('success', 'Model updated.');
    }

    public function destroy(TalentModel $model, ActivityLogger $logger)
    {
        $logger->deleted('Model', $model->id, $model->name);
        $model->delete();
        return redirect()->route('models.index')->with('success', 'Model archived.');
    }
}
