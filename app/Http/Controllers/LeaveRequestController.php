<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = LeaveRequest::with('employee')
            ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->leave_type,  fn($q) => $q->where('leave_type', $request->leave_type))
            ->when($request->month, fn($q) => $q->whereMonth('start_date', $request->month))
            ->when($request->year,  fn($q) => $q->whereYear('start_date',  $request->year))
            ->orderByDesc('start_date')
            ->paginate(20)->withQueryString();

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        return view('leave-requests.index', compact('requests', 'employees'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $preselect = $request->integer('employee_id', 0);
        return view('leave-requests.create', compact('employees', 'preselect'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type'  => 'required|in:V,S,P,D,U',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string|min:5|max:1000',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $days  = LeaveRequest::countWorkingDays($start, $end);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create(array_merge($data, ['days' => $days]));

        // Auto-create leave records immediately (no approval needed)
        $leaveRequest->createLeaveRecords();

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', "บันทึกใบลาเรียบร้อย สร้างรายการลา {$days} วัน");
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('employee');
        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('leave-requests.edit', compact('leaveRequest', 'employees'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type'  => 'required|in:V,S,P,D,U',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string|min:5|max:1000',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $days = LeaveRequest::countWorkingDays(
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date'])
        );

        if ($request->hasFile('attachment')) {
            if ($leaveRequest->attachment) Storage::disk('public')->delete($leaveRequest->attachment);
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        // Remove old leave records and recreate
        \App\Models\LeaveRecord::where('employee_id', $leaveRequest->employee_id)
            ->whereBetween('leave_date', [$leaveRequest->start_date, $leaveRequest->end_date])
            ->where('leave_type', $leaveRequest->leave_type)
            ->delete();

        $leaveRequest->update(array_merge($data, ['days' => $days]));
        $leaveRequest->createLeaveRecords();

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'แก้ไขใบลาเรียบร้อย');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        // Remove associated leave records
        \App\Models\LeaveRecord::where('employee_id', $leaveRequest->employee_id)
            ->whereBetween('leave_date', [$leaveRequest->start_date, $leaveRequest->end_date])
            ->where('leave_type', $leaveRequest->leave_type)
            ->delete();

        if ($leaveRequest->attachment) Storage::disk('public')->delete($leaveRequest->attachment);
        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('success', 'ลบใบลาเรียบร้อย');
    }

    /** AJAX: calculate working days */
    public function calcDays(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);
        return response()->json([
            'days' => LeaveRequest::countWorkingDays(
                Carbon::parse($data['start_date']),
                Carbon::parse($data['end_date'])
            ),
        ]);
    }
}
