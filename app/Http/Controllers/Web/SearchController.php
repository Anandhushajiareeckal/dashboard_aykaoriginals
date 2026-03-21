<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{TalentModel, Brand, Project};
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $req)
    {
        $q = $req->input('q', '');

        if (strlen($q) < 2) {
            return view('search', ['q' => $q, 'models' => collect(), 'brands' => collect(), 'projects' => collect()]);
        }

        $models = TalentModel::where('name', 'like', "%$q%")
            ->orWhere('location', 'like', "%$q%")
            ->limit(6)->get();

        $brands = Brand::where('name', 'like', "%$q%")
            ->orWhere('contact_name', 'like', "%$q%")
            ->limit(6)->get();

        $projects = Project::with('brand')
            ->where('title', 'like', "%$q%")
            ->orWhere('category', 'like', "%$q%")
            ->limit(6)->get();

        return view('search', compact('q', 'models', 'brands', 'projects'));
    }
}
