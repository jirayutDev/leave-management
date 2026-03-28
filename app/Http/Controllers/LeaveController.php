<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRecord;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $employees = Employee::with([
            'leaveRecords' => fn ($q) => $q->whereMonth('leave_date', $month)->whereYear('leave_date', $year),
            'leaveQuotas'  => fn ($q) => $q->where('year', $year),
        ])->where('status', 'active')->orderBy('employee_code')->get();

        // Build calendar grid (working days only Mon-Fri)
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

        return view('leave.index', compact('employees', 'month', 'year', 'daysInMonth'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_date'  => 'required|date',
            'leave_type'  => 'required|in:V,S,P,D,U',
            'note'        => 'nullable|string|max:255',
        ]);

        LeaveRecord::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'leave_date' => $data['leave_date']],
            ['leave_type' => $data['leave_type'], 'note' => $data['note'] ?? null]
        );

        return back()->with('success', 'บันทึกการลาสำเร็จ');
    }

    public function destroy(LeaveRecord $leaveRecord)
    {
        $leaveRecord->delete();
        return back()->with('success', 'ลบรายการลาสำเร็จ');
    }
}
