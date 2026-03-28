<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\LeaveRecord;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LeaveExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private int $month,
        private int $year
    ) {}

    public function title(): string
    {
        return Carbon::create($this->year, $this->month)->format('F Y');
    }

    public function array(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $monthName   = Carbon::create($this->year, $this->month)->format('F Y');

        $leaveTypes = ['V', 'S', 'P', 'D', 'U'];
        $fields     = ['V' => 'vacation', 'S' => 'sick', 'P' => 'personal', 'D' => 'disability', 'U' => 'unpaid'];

        // Build day headers with day-of-week abbrev
        $dayHeaders = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($d <= $daysInMonth) {
                $dayHeaders[] = Carbon::create($this->year, $this->month, $d)->format('D');
            } else {
                $dayHeaders[] = '';
            }
        }

        $rows = [];

        // Row 1: Month header
        $rows[] = array_merge([$monthName, ''], array_fill(0, 41, ''));

        // Row 2: blank
        $rows[] = array_fill(0, 44, '');

        // Row 3: legend
        $rows[] = array_merge(['Employee', '', 'V = Vacation, S = Sick, P = Personal, D = Disability, U = Unpaid'], array_fill(0, 41, ''));

        // Row 4: day-of-week names
        $dowRow = ['', ''];
        for ($d = 1; $d <= 31; $d++) {
            $dowRow[] = ($d <= $daysInMonth)
                ? Carbon::create($this->year, $this->month, $d)->format('D')
                : '';
        }
        $dowRow = array_merge($dowRow, ['', '', '', '', '', '', '', '', '', '', '']);
        $rows[] = $dowRow;

        // Row 5: column headers
        $headerRow = ['ID', 'Name'];
        for ($d = 1; $d <= 31; $d++) $headerRow[] = $d;
        $headerRow = array_merge($headerRow, ['V', 'S', 'P', 'D', 'U', '', 'V', 'S', 'P', 'D', 'U']);
        $rows[] = $headerRow;

        // Fetch all leave records for this month/year
        $records = LeaveRecord::with('employee')
            ->whereMonth('leave_date', $this->month)
            ->whereYear('leave_date', $this->year)
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::with(['leaveQuotas' => fn($q) => $q->where('year', $this->year)])
            ->where('status', 'active')
            ->orderBy('employee_code')
            ->get();

        // Totals accumulator
        $grandTotals = array_fill_keys($leaveTypes, 0);

        foreach ($employees as $emp) {
            $empRecords = $records->get($emp->id, collect());
            $byDay      = $empRecords->keyBy(fn($r) => $r->leave_date->day);
            $quota      = $emp->leaveQuotas->first();

            $row = [$emp->employee_code, $emp->name];

            // Daily cells
            for ($d = 1; $d <= 31; $d++) {
                if ($d <= $daysInMonth) {
                    $rec   = $byDay->get($d);
                    $row[] = $rec ? $rec->leave_type : '';
                } else {
                    $row[] = '';
                }
            }

            // Monthly totals
            $monthTotals = [];
            foreach ($leaveTypes as $t) {
                $cnt            = $empRecords->where('leave_type', $t)->count();
                $monthTotals[]  = $cnt ?: 0;
                $grandTotals[$t] += $cnt;
            }
            $row = array_merge($row, $monthTotals);
            $row[] = ''; // separator

            // Quota
            foreach ($leaveTypes as $t) {
                $row[] = $quota ? $quota->{$fields[$t]} : 0;
            }

            $rows[] = $row;
        }

        // Grand total row
        $totalRow = ['Total', ''];
        $totalRow = array_merge($totalRow, array_fill(0, 31, ''));
        foreach ($leaveTypes as $t) $totalRow[] = $grandTotals[$t] ?: 0;
        $totalRow[] = '';
        $totalRow   = array_merge($totalRow, array_fill(0, 5, ''));
        $rows[]     = $totalRow;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        // Header row (row 1) — month title
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 5 — column header
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2d5a8e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle("A5:{$sheet->getHighestColumn()}5")->applyFromArray($headerStyle);

        // Weekend columns — light grey background
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($this->year, $this->month, $d);
            if (in_array($date->dayOfWeek, [0, 6])) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 2);
                $sheet->getStyle("{$col}6:{$col}{$lastRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e9ecef']],
                ]);
            }
        }

        // Total separators (cols AH–AL = totals, AM = blank, AN–AR = quota)
        $sheet->getStyle("AH5:AL5")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ]);
        $sheet->getStyle("AN5:AR5")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ]);

        // Last row (Total) bold
        $sheet->getStyle("A{$lastRow}:{$sheet->getHighestColumn()}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8f9fa']],
        ]);

        // Freeze panes at C6
        $sheet->freezePane('C6');

        return [];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 14, 'B' => 22];
        // Days: columns C to AG (3 to 33)
        for ($i = 3; $i <= 33; $i++) {
            $col           = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $widths[$col]  = 4;
        }
        // Totals + quota
        foreach (['AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR'] as $col) {
            $widths[$col] = 5;
        }
        return $widths;
    }
}
