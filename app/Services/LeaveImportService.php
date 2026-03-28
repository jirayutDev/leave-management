<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveQuota;
use App\Models\LeaveRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveImportService
{
    /**
     * Parse and import a CSV file in the BITNANCE leave tracker format.
     *
     * Format:
     *   Row 0: "Month Year" header
     *   Row 1: blank
     *   Row 2: legend row
     *   Row 3: column headers (ID, Name, 1,2,...,31, V,S,P,D,U, blank, V,S,P,D,U)
     *   Row 4+: employee data
     */
    public function import(string $filePath, ?string $fileName = null): array
    {
        $rows = $this->parseCsv($filePath);

        // Determine month/year from first cell
        $headerCell = trim($rows[0][0] ?? '');
        [$month, $year] = $this->parseMonthYear($headerCell);

        $imported  = 0;
        $skipped   = 0;
        $errors    = [];

        // Data rows start at index 4 (after 3 header rows + column names row)
        $dataRows = array_slice($rows, 4);

        DB::transaction(function () use ($dataRows, $month, $year, $fileName, &$imported, &$skipped, &$errors) {
            foreach ($dataRows as $rowIndex => $row) {
                $employeeCode = trim($row[0] ?? '');
                $name         = trim($row[1] ?? '');

                // Skip empty rows or totals row
                if ($employeeCode === '' || strtolower($employeeCode) === 'total') {
                    continue;
                }

                // Find or create employee
                $employee = Employee::firstOrCreate(
                    ['employee_code' => $employeeCode],
                    ['name' => $name, 'status' => 'active']
                );

                // Sync name if changed
                if ($employee->name !== $name && $name !== '') {
                    $employee->update(['name' => $name]);
                }

                // Import daily leave records (columns 2–32 => days 1–31)
                for ($day = 1; $day <= 31; $day++) {
                    $colIndex  = $day + 1; // column offset
                    $cellValue = strtoupper(trim($row[$colIndex] ?? ''));

                    if (!in_array($cellValue, ['V', 'S', 'P', 'D', 'U'])) {
                        continue;
                    }

                    try {
                        $date = Carbon::createFromDate($year, $month, $day);
                    } catch (\Exception $e) {
                        continue; // Day doesn't exist in this month
                    }

                    LeaveRecord::updateOrCreate(
                        ['employee_id' => $employee->id, 'leave_date' => $date->toDateString()],
                        ['leave_type' => $cellValue, 'imported_from' => $fileName]
                    );
                }

                // Import quota (columns 39–43 => V, S, P, D, U)
                $quotaMap = [39 => 'vacation', 40 => 'sick', 41 => 'personal', 42 => 'disability', 43 => 'unpaid'];
                $quotaData = ['employee_id' => $employee->id, 'year' => $year];
                foreach ($quotaMap as $colIdx => $field) {
                    $val = trim($row[$colIdx] ?? '');
                    $quotaData[$field] = is_numeric($val) ? (int) $val : 0;
                }

                LeaveQuota::updateOrCreate(
                    ['employee_id' => $employee->id, 'year' => $year],
                    $quotaData
                );

                $imported++;
            }
        });

        return [
            'month'    => $month,
            'year'     => $year,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    private function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Parse "December 2023" or "January 2024" style string into [month, year].
     * Falls back to current month/year.
     */
    private function parseMonthYear(string $raw): array
    {
        $raw = trim($raw, " \t\n\r\0\x0B,");
        try {
            $date = Carbon::parse('1 ' . $raw);
            return [$date->month, $date->year];
        } catch (\Exception $e) {
            return [now()->month, now()->year];
        }
    }
}
