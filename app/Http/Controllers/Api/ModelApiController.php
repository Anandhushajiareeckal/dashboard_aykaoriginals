<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\TalentModel;
use Illuminate\Http\Request;

class ModelApiController extends Controller {
    public function index(Request $req) {
        $q = TalentModel::query();
        if ($req->name) $q->where('name','like','%'.$req->name.'%');
        if ($req->location) $q->where('location','like','%'.$req->location.'%');
        if ($req->status) $q->where('status',$req->status);
        if ($req->category) $q->whereJsonContains('categories',$req->category);
        $models = $q->latest()->paginate(20);
        return response()->json($models);
    }
    public function show(TalentModel $model) {
        $model->load('projects');
        return response()->json($model);
    }
}
