<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, User};
use Illuminate\Http\JsonResponse;

class ActivityGraphController extends Controller
{
    public function data(): JsonResponse
    {
        $days   = collect(range(6,0))->map(fn($d)=>now()->subDays($d)->format('Y-m-d'));
        $labels = $days->map(fn($d)=>now()->parse($d)->format('D d'));

        $personalities = [
            'Sanchu'  => ['color'=>'#C9A96E','emoji'=>'crown', 'title'=>'The Boss'],
            'Ashima'  => ['color'=>'#5B8FE8','emoji'=>'star',  'title'=>'The Hustler'],
            'Ananthu' => ['color'=>'#E87B5B','emoji'=>'rocket','title'=>'The Rookie'],
            'Admin'   => ['color'=>'#6B5BE8','emoji'=>'ghost', 'title'=>'The Ghost Admin'],
        ];

        $datasets = User::all()->map(function($user) use ($days,$personalities) {
            $counts = $days->map(fn($day)=>ActivityLog::where('user_id',$user->id)->whereDate('created_at',$day)->count());
            $p = $personalities[$user->name] ?? ['color'=>'#8A8880','emoji'=>'alien','title'=>'Mystery User'];
            return [
                'label'            => $user->name,
                'title'            => $p['title'],
                'emoji'            => $p['emoji'],
                'data'             => $counts->values()->toArray(),
                'total'            => $counts->sum(),
                'borderColor'      => $p['color'],
                'backgroundColor'  => $p['color'].'22',
                'tension'          => 0.4,
                'pointRadius'      => 6,
                'pointHoverRadius' => 9,
                'borderWidth'      => 2.5,
                'fill'             => true,
            ];
        })->values();

        return response()->json(['labels'=>$labels->values(),'datasets'=>$datasets]);
    }
}