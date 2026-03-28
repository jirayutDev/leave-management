<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\Employee;
use App\Models\LeaveRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function showForm(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $totalEmployees      = Employee::where('status', 'active')->count();
        $leaveRecordsInMonth = LeaveRecord::whereMonth('leave_date', $month)->whereYear('leave_date', $year)->get();
        $totalLeaveThisMonth = $leaveRecordsInMonth->count();
        $leaveCounts         = $leaveRecordsInMonth->groupBy('leave_type')->map->count();

        return view('export.index', compact('month', 'year', 'totalEmployees', 'totalLeaveThisMonth', 'leaveCounts'));
    }

    /** Export as Excel (.xlsx) */
    public function exportExcel(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ]);

        $month    = (int) $data['month'];
        $year     = (int) $data['year'];
        $filename = 'leave_' . Carbon::create($year, $month)->format('Y_m_F') . '.xlsx';

        return Excel::download(new LeaveExport($month, $year), $filename);
    }

    /** Export as CSV (BITNANCE format) */
    public function exportCsv(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ]);

        $month       = (int) $data['month'];
        $year        = (int) $data['year'];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $monthName   = Carbon::create($year, $month)->format('F Y');
        $filename    = 'leave_' . Carbon::create($year, $month)->format('Y_m_F') . '.csv';

        $leaveTypes = ['V', 'S', 'P', 'D', 'U'];
        $fields     = ['V' => 'vacation', 'S' => 'sick', 'P' => 'personal', 'D' => 'disability', 'U' => 'unpaid'];

        $records = LeaveRecord::with('employee')
            ->whereMonth('leave_date', $month)
            ->whereYear('leave_date', $year)
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::with(['leaveQuotas' => fn($q) => $q->where('year', $year)])
            ->where('status', 'active')
            ->orderBy('employee_code')
            ->get();

        $output = fopen('php://temp', 'r+');

        // Row 1: month title + padding
        $titleRow = array_fill(0, 44, '');
        $titleRow[0] = $monthName;
        fputcsv($output, $titleRow);

        // Row 2: blank
        fputcsv($output, array_fill(0, 44, ''));

        // Row 3: legend
        $legendRow    = array_fill(0, 44, '');
        $legendRow[0] = 'Employee';
        $legendRow[2] = 'V = Vacation, S = Sick, P = Personal, D = Disability, U = Unpaid';
        fputcsv($output, $legendRow);

        // Row 4: day-of-week
        $dowRow = ['', ''];
        for ($d = 1; $d <= 31; $d++) {
            $dowRow[] = ($d <= $daysInMonth)
                ? Carbon::create($year, $month, $d)->format('D')
                : '';
        }
        $dowRow = array_merge($dowRow, ['V', 'S', 'P', 'D', 'U', '', 'V', 'S', 'P', 'D', 'U']);
        fputcsv($output, $dowRow);

        // Row 5: column headers
        $headerRow = ['ID', 'Name'];
        for ($d = 1; $d <= 31; $d++) $headerRow[] = $d;
        $headerRow = array_merge($headerRow, ['V', 'S', 'P', 'D', 'U', '', 'V', 'S', 'P', 'D', 'U']);
        fputcsv($output, $headerRow);

        $grandTotals = array_fill_keys($leaveTypes, 0);

        foreach ($employees as $emp) {
            $empRecords = $records->get($emp->id, collect());
            $byDay      = $empRecords->keyBy(fn($r) => $r->leave_date->day);
            $quota      = $emp->leaveQuotas->first();

            $row = [$emp->employee_code, $emp->name];
            for ($d = 1; $d <= 31; $d++) {
                $row[] = ($d <= $daysInMonth && $byDay->has($d)) ? $byDay->get($d)->leave_type : '';
            }

            foreach ($leaveTypes as $t) {
                $cnt            = $empRecords->where('leave_type', $t)->count();
                $row[]          = $cnt ?: 0;
                $grandTotals[$t] += $cnt;
            }
            $row[] = '';
            foreach ($leaveTypes as $t) {
                $row[] = $quota ? $quota->{$fields[$t]} : 0;
            }
            fputcsv($output, $row);
        }

        // Total row
        $totalRow = array_fill(0, 44, '');
        $totalRow[0] = 'Total';
        $i = 33;
        foreach ($leaveTypes as $t) {
            $totalRow[$i++] = $grandTotals[$t] ?: 0;
        }
        fputcsv($output, $totalRow);

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
