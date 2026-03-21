<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Meeting;

class MeetingApiController extends Controller {
    public function index() {
        $meetings = Meeting::with('brand','project')->where('meeting_at','>=',now())->orderBy('meeting_at')->paginate(20);
        return response()->json($meetings);
    }
}
