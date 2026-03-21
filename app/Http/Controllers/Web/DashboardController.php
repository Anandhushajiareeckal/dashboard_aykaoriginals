<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{TalentModel, Project, Invoice, Expense};

class DashboardController extends Controller {
    public function __invoke() {
        $stats = [
            'total_models'     => TalentModel::count(),
            'active_projects'  => Project::where('status','Active')->count(),
            'monthly_revenue'  => Invoice::where('status','Paid')->whereMonth('paid_date', now()->month)->sum('total'),
            'pending_payments' => Invoice::whereIn('status',['Sent','Overdue'])->sum('total'),
        ];
        $revenueChart = $this->revenueChartData();
        $projectStatus = Project::select('status', \DB::raw('count(*) as count'))->groupBy('status')->get();
        $recentProjects = Project::with('brand')->latest()->take(5)->get();
        $recentActivity = \App\Models\ActivityLog::with('user')->latest()->take(8)->get();
        return view('dashboard', compact('stats','revenueChart','projectStatus','recentProjects','recentActivity'));
    }
    private function revenueChartData(): array {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = [
                'month'   => $date->format('M'),
                'revenue' => Invoice::where('status','Paid')->whereYear('paid_date',$date->year)->whereMonth('paid_date',$date->month)->sum('total'),
                'expense' => Expense::whereYear('expense_date',$date->year)->whereMonth('expense_date',$date->month)->sum('amount'),
            ];
        }
        return $data;
    }
}
