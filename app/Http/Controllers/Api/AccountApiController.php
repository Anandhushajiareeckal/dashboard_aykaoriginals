<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Invoice, Expense};

class AccountApiController extends Controller {
    public function invoices() {
        return response()->json(Invoice::with('brand','project')->latest()->paginate(20));
    }
    public function summary() {
        return response()->json([
            'total_revenue' => Invoice::where('status','Paid')->sum('total'),
            'pending'       => Invoice::whereIn('status',['Sent','Overdue'])->sum('total'),
            'total_expense' => Expense::sum('amount'),
        ]);
    }
}
