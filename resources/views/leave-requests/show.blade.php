@extends('layouts.app')
@section('title', 'รายละเอียดคำขอลา')
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0">รายละเอียดคำขอลา</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil me-1"></i>แก้ไข
        </a>
        <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"
                    data-confirm="ลบคำขอลาของ {{ $leaveRequest->employee->name }}?"
                    data-confirm-icon="warning">
                <i class="bi bi-trash me-1"></i>ลบ
            </button>
        </form>
    </div>
</div>

<div class="row g-4 align-items-start">
    <div class="col-12 col-lg-7">
        <div class="card p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 pb-3 mb-4 border-bottom">
                <div>
                    <div class="fw-bold" style="font-size:1.05rem;">{{ $leaveRequest->employee->name }}</div>
                    <div class="text-muted small mt-1">{{ $leaveRequest->employee->employee_code }}
                        @if($leaveRequest->employee->department)
                            <span class="mx-1">·</span>{{ $leaveRequest->employee->department }}
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted" style="font-size:.75rem;">ยื่นเมื่อ</div>
                    <div class="small fw-semibold">{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-4 text-muted small fw-medium">ประเภทการลา</div>
                <div class="col-sm-8">
                    <span class="badge badge-{{ $leaveRequest->leave_type }} me-1">{{ $leaveRequest->leave_type }}</span>
                    {{ \App\Models\LeaveRecord::$typeLabels[$leaveRequest->leave_type] }}
                </div>

                <div class="col-sm-4 text-muted small fw-medium">วันที่เริ่มลา</div>
                <div class="col-sm-8 fw-semibold">{{ $leaveRequest->start_date->format('d/m/Y') }}</div>

                <div class="col-sm-4 text-muted small fw-medium">วันที่สิ้นสุด</div>
                <div class="col-sm-8 fw-semibold">{{ $leaveRequest->end_date->format('d/m/Y') }}</div>

                <div class="col-sm-4 text-muted small fw-medium">จำนวนวัน</div>
                <div class="col-sm-8">
                    <span class="badge bg-primary fs-6 px-3">{{ $leaveRequest->days }} วัน</span>
                    <span class="text-muted small ms-2">วันทำงาน</span>
                </div>

                <div class="col-sm-4 text-muted small fw-medium">สาเหตุการลา</div>
                <div class="col-sm-8">
                    <div class="rounded-3 p-3 small" style="background:#f8fafc; border:1px solid #e2e8f0;">{{ $leaveRequest->reason }}</div>
                </div>

                @if($leaveRequest->attachment)
                <div class="col-sm-4 text-muted small fw-medium">ไฟล์แนบ</div>
                <div class="col-sm-8">
                    <a href="{{ Storage::url($leaveRequest->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-paperclip me-1"></i>เปิดไฟล์แนบ
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card p-4 text-center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #bbf7d0;">
            <i class="bi bi-check-circle-fill mb-3" style="font-size:2.5rem; color:#16a34a;"></i>
            <div class="fw-bold mb-1" style="color:#15803d; font-size:1.05rem;">อนุมัติแล้ว</div>
            <div class="small text-muted mb-4">สร้างรายการลา {{ $leaveRequest->days }} วันในตารางวันลาแล้ว</div>
            <a href="{{ route('leave.index', ['month' => $leaveRequest->start_date->month, 'year' => $leaveRequest->start_date->year]) }}"
               class="btn btn-sm" style="background:#16a34a; color:#fff; border:none;">
                <i class="bi bi-calendar3 me-1"></i>ดูตารางวันลา
            </a>
        </div>
    </div>
</div>
@endsection
