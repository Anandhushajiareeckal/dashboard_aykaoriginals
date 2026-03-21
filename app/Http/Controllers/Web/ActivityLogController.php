<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, User};
use Illuminate\Http\Request;
class ActivityLogController extends Controller {
    public function index(Request $req) {
        $q = ActivityLog::with('user')->latest();
        if ($req->user_id)   $q->where('user_id', $req->user_id);
        if ($req->action)    $q->where('action', $req->action);
        if ($req->module)    $q->where('module', $req->module);
        if ($req->ip)        $q->where('ip_address', 'like', '%'.$req->ip.'%');
        if ($req->date_from) $q->whereDate('created_at', '>=', $req->date_from);
        if ($req->date_to)   $q->whereDate('created_at', '<=', $req->date_to);
        if ($req->search)    $q->where('description', 'like', '%'.$req->search.'%');
        $logs       = $q->paginate(50)->withQueryString();
        $users      = User::orderBy('name')->get();
        $actions    = ActivityLog::select('action')->distinct()->pluck('action');
        $modules    = ActivityLog::select('module')->whereNotNull('module')->distinct()->pluck('module');
        $todayCount = ActivityLog::whereDate('created_at', today())->count();
        $loginCount = ActivityLog::where('action', 'login')->whereDate('created_at', today())->count();
        $totalCount = ActivityLog::count();
        $uniqueIps  = ActivityLog::whereDate('created_at', today())->distinct('ip_address')->count('ip_address');
        return view('activity-log.index', compact('logs', 'users', 'actions', 'modules', 'todayCount', 'loginCount', 'totalCount', 'uniqueIps'));
    }
}
