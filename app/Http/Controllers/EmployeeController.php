<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveQuota;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('employee_code', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('employee_code')
            ->paginate(20)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_code' => 'required|unique:employees,employee_code',
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|unique:employees,email',
            'department'    => 'nullable|string|max:255',
            'position'      => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'status'        => 'required|in:active,inactive',
        ]);

        $employee = Employee::create($data);

        // Create default leave quota for current year
        LeaveQuota::firstOrCreate(
            ['employee_id' => $employee->id, 'year' => now()->year],
            ['vacation' => 6, 'sick' => 30, 'personal' => 3, 'disability' => 0, 'unpaid' => 0]
        );

        return redirect()->route('employees.index')->with('success', 'เพิ่มพนักงานสำเร็จ');
    }

    public function show(Employee $employee)
    {
        $year   = request('year', now()->year);
        $quota  = $employee->quotaForYear($year);
        $leaveTypes = ['V' => 'vacation', 'S' => 'sick', 'P' => 'personal', 'D' => 'disability', 'U' => 'unpaid'];

        $leaveUsed = [];
        foreach ($leaveTypes as $type => $field) {
            $leaveUsed[$type] = $employee->usedLeaveInYear($type, $year);
        }

        $leaveRecords = $employee->leaveRecords()
            ->whereYear('leave_date', $year)
            ->orderBy('leave_date')
            ->paginate(31);

        return view('employees.show', compact('employee', 'year', 'quota', 'leaveUsed', 'leaveRecords', 'leaveTypes'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'employee_code' => "required|unique:employees,employee_code,{$employee->id}",
            'name'          => 'required|string|max:255',
            'email'         => "nullable|email|unique:employees,email,{$employee->id}",
            'department'    => 'nullable|string|max:255',
            'position'      => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'status'        => 'required|in:active,inactive',
        ]);

        $employee->update($data);

        return redirect()->route('employees.show', $employee)->with('success', 'อัปเดตข้อมูลสำเร็จ');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'ลบพนักงานสำเร็จ');
    }

    // ─── Quota management ────────────────────────────────────────────────────

    public function editQuota(Employee $employee)
    {
        $years  = range(now()->year, now()->year - 3);
        $year   = request('year', now()->year);
        $quota  = LeaveQuota::firstOrCreate(
            ['employee_id' => $employee->id, 'year' => $year],
            ['vacation' => 6, 'sick' => 30, 'personal' => 3, 'disability' => 0, 'unpaid' => 0]
        );

        return view('employees.quota', compact('employee', 'quota', 'year', 'years'));
    }

    public function updateQuota(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'year'       => 'required|integer|min:2020|max:2099',
            'vacation'   => 'required|integer|min:0',
            'sick'       => 'required|integer|min:0',
            'personal'   => 'required|integer|min:0',
            'disability' => 'required|integer|min:0',
            'unpaid'     => 'required|integer|min:0',
        ]);

        LeaveQuota::updateOrCreate(
            ['employee_id' => $employee->id, 'year' => $data['year']],
            $data
        );

        return redirect()->route('employees.show', ['employee' => $employee, 'year' => $data['year']])
            ->with('success', 'อัปเดตโควต้าวันลาสำเร็จ');
    }
}
