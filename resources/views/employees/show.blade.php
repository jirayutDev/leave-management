@extends('layouts.app')
@section('title', $employee->name)
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title mb-0">{{ $employee->name }}</h1>
            <span class="text-muted small">{{ $employee->employee_code }}</span>
        </div>
        <span class="badge ms-1" style="{{ $employee->status === 'active' ? 'background:#dcfce7;color:#166534;' : 'background:#f1f5f9;color:#64748b;' }} font-weight:600;">
            {{ $employee->status === 'active' ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('employees.quota.edit', $employee) }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-calendar-plus me-1"></i>จัดการโควต้า
        </a>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>แก้ไข
        </a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
            <i class="bi bi-plus-lg me-1"></i>เพิ่มการลา
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em;">ข้อมูลส่วนตัว</h6>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted fw-normal">รหัส</dt>       <dd class="col-7 fw-semibold">{{ $employee->employee_code }}</dd>
                <dt class="col-5 text-muted fw-normal">อีเมล</dt>      <dd class="col-7">{{ $employee->email ?? '—' }}</dd>
                <dt class="col-5 text-muted fw-normal">แผนก</dt>       <dd class="col-7">{{ $employee->department ?? '—' }}</dd>
                <dt class="col-5 text-muted fw-normal">ตำแหน่ง</dt>   <dd class="col-7">{{ $employee->position ?? '—' }}</dd>
                <dt class="col-5 text-muted fw-normal">วันเริ่มงาน</dt><dd class="col-7">{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                <h6 class="fw-semibold text-muted mb-0" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em;">โควต้า {{ $year }}</h6>
                <form method="GET">
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="width:82px;">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
            @if($quota)
                <div class="row g-2 text-center">
                    @foreach(['V'=>'vacation','S'=>'sick','P'=>'personal','D'=>'disability','U'=>'unpaid'] as $type=>$field)
                    @php $used = $leaveUsed[$type] ?? 0; $q = $quota->$field; @endphp
                    <div class="col">
                        <div class="rounded-3 p-2" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <span class="badge badge-{{ $type }} d-block mb-1" style="font-size:.65rem;">{{ $type }}</span>
                            <div class="fw-bold small {{ $used > $q ? 'text-danger' : 'text-dark' }}">{{ $used }}/{{ $q }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small mb-0">ยังไม่มีโควต้า <a href="{{ route('employees.quota.edit', $employee) }}">ตั้งค่า</a></p>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header-bar">
        <span class="fw-semibold" style="font-size:.9rem;"><i class="bi bi-clock-history me-2 text-primary"></i>ประวัติการลา {{ $year }}</span>
    </div>
    @if($leaveRecords->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>ไม่มีข้อมูลการลา
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>วันที่</th><th>ประเภท</th><th class="d-none d-sm-table-cell">หมายเหตุ</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($leaveRecords as $rec)
                    <tr>
                        <td class="text-nowrap">{{ $rec->leave_date->format('d/m/Y') }}</td>
                        <td><span class="badge badge-{{ $rec->leave_type }}">{{ \App\Models\LeaveRecord::$typeLabels[$rec->leave_type] }}</span></td>
                        <td class="d-none d-sm-table-cell text-muted">{{ $rec->note ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('leave.destroy', $rec) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm btn-icon"
                                        data-confirm="ลบรายการลา {{ $rec->leave_date->format('d/m/Y') }}?"
                                        data-confirm-icon="warning">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">{{ $leaveRecords->links() }}</div>
    @endif
</div>

<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">เพิ่มการลา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('leave.store') }}">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label">วันที่ลา</label>
                        <input type="date" name="leave_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภทการลา</label>
                        <select name="leave_type" class="form-select" required>
                            @foreach(\App\Models\LeaveRecord::$typeLabels as $k => $v)
                                <option value="{{ $k }}">{{ $k }} — {{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">หมายเหตุ</label>
                        <input type="text" name="note" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
