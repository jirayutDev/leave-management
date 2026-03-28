<?php

namespace App\Http\Controllers;

use App\Services\LeaveImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(private LeaveImportService $importService) {}

    public function showForm()
    {
        return view('import.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file     = $request->file('csv_file');
        $fileName = $file->getClientOriginalName();
        $path     = $file->getRealPath();

        try {
            $result = $this->importService->import($path, $fileName);

            $msg = "นำเข้าข้อมูลสำเร็จ: {$result['imported']} รายการ "
                 . "เดือน {$result['month']}/{$result['year']}";

            return back()->with('success', $msg)->with('importResult', $result);
        } catch (\Throwable $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
