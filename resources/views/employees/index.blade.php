@extends('layouts.app')
@section('title', 'พนักงาน')
@section('content')

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-people"></i>พนักงานทั้งหมด</h1>
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มพนักงาน
    </a>
</div>

<div class="filter-bar">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-sm-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหาชื่อหรือรหัส" value="{{ request('search') }}">
        </div>
        <div class="col-6 col-sm-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- สถานะ --</option>
                <option value="active"   {{ request('status')=='active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-sm-auto d-flex gap-2">
            <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>รหัส / ชื่อ</th>
                    <th class="d-none d-md-table-cell">แผนก</th>
                    <th class="d-none d-lg-table-cell">ตำแหน่ง</th>
                    <th class="text-end d-none d-sm-table-cell">เงินเดือน</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            @forelse($employees as $emp)
                <tr>
                    <td>
                        <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none fw-semibold text-dark d-block lh-sm">{{ $emp->name }}</a>
                        <span class="text-muted" style="font-size:.78rem;">{{ $emp->employee_code }}</span>
                    </td>
                    <td class="d-none d-md-table-cell text-muted">{{ $emp->department ?? '—' }}</td>
                    <td class="d-none d-lg-table-cell text-muted">{{ $emp->position ?? '—' }}</td>
                    <td class="text-end d-none d-sm-table-cell text-muted">{{ number_format($emp->base_salary, 0) }}</td>
                    <td class="text-center">
                        @if($emp->status === 'active')
                            <span class="badge" style="background:#dcfce7; color:#166534; font-weight:600;">Active</span>
                        @else
                            <span class="badge" style="background:#f1f5f9; color:#64748b; font-weight:600;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('employees.quota.edit', $emp) }}" class="btn btn-sm btn-outline-info btn-icon" title="โควต้า"><i class="bi bi-calendar-plus"></i></a>
                        <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-warning btn-icon" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('employees.destroy', $emp) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger btn-icon" title="ลบ"
                                    data-confirm="ลบพนักงาน {{ $emp->name }}?" data-confirm-icon="warning">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>ไม่พบข้อมูลพนักงาน
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">{{ $employees->links() }}</div>
</div>
@endsection
