<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::where('status', 'active')->count();
        $todayLeaves    = LeaveRecord::whereDate('leave_date', today())->count();
        $monthLeaves    = LeaveRecord::whereMonth('leave_date', now()->month)
            ->whereYear('leave_date', now()->year)->count();

        $leaveByType = LeaveRecord::whereMonth('leave_date', now()->month)
            ->whereYear('leave_date', now()->year)
            ->selectRaw('leave_type, COUNT(*) as total')
            ->groupBy('leave_type')
            ->pluck('total', 'leave_type');

        $recentLeaves = LeaveRecord::with('employee')
            ->whereMonth('leave_date', now()->month)
            ->whereYear('leave_date', now()->year)
            ->orderByDesc('leave_date')
            ->limit(15)
            ->get();

        return view('dashboard', compact(
            'totalEmployees', 'todayLeaves', 'monthLeaves', 'leaveByType', 'recentLeaves'
        ));
    }
}
