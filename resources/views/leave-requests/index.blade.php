@extends('layouts.app')
@section('title', 'คำขอลา')
@section('content')

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-file-earmark-text"></i>คำขอลา</h1>
    <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มคำขอลา
    </a>
</div>

<div class="filter-bar">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-sm-4">
            <select name="employee_id" class="form-select form-select-sm">
                <option value="">-- พนักงานทั้งหมด --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-sm-2">
            <select name="leave_type" class="form-select form-select-sm">
                <option value="">-- ประเภท --</option>
                @foreach(\App\Models\LeaveRecord::$typeLabels as $k => $v)
                    <option value="{{ $k }}" {{ request('leave_type') == $k ? 'selected' : '' }}>{{ $k }} – {{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-3 col-sm-2">
            <select name="month" class="form-select form-select-sm">
                <option value="">-- เดือน --</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->format('M') }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-3 col-sm-2">
            <select name="year" class="form-select form-select-sm">
                <option value="">-- ปี --</option>
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>พนักงาน</th>
                    <th>ประเภท</th>
                    <th class="d-none d-md-table-cell">วันที่ลา</th>
                    <th class="text-center">วัน</th>
                    <th class="d-none d-lg-table-cell">สาเหตุ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $req)
            <tr>
                <td>
                    <a href="{{ route('leave-requests.show', $req) }}" class="text-decoration-none fw-semibold text-dark d-block lh-sm">
                        {{ $req->employee->name }}
                    </a>
                    <span class="text-muted" style="font-size:.78rem;">{{ $req->employee->employee_code }}</span>
                </td>
                <td>
                    <span class="badge badge-{{ $req->leave_type }}">{{ $req->leave_type }}</span>
                    <div class="text-muted d-none d-sm-block" style="font-size:.75rem; margin-top:2px;">{{ \App\Models\LeaveRecord::$typeLabels[$req->leave_type] }}</div>
                </td>
                <td class="d-none d-md-table-cell text-muted" style="font-size:.85rem;">
                    {{ $req->start_date->format('d/m/Y') }}
                    @if(!$req->start_date->eq($req->end_date))
                        <span class="mx-1">–</span>{{ $req->end_date->format('d/m/Y') }}
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge bg-primary rounded-pill px-2">{{ $req->days }}</span>
                </td>
                <td class="d-none d-lg-table-cell text-muted" style="max-width:200px; font-size:.85rem;">
                    <div class="text-truncate">{{ $req->reason }}</div>
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('leave-requests.show', $req) }}" class="btn btn-sm btn-outline-primary btn-icon" title="ดู"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('leave-requests.edit', $req) }}" class="btn btn-sm btn-outline-warning btn-icon" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('leave-requests.destroy', $req) }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger btn-icon" title="ลบ"
                                data-confirm="ลบคำขอลาของ {{ $req->employee->name }}?"
                                data-confirm-icon="warning">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    ยังไม่มีคำขอลา
                    <div class="mt-3">
                        <a href="{{ route('leave-requests.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>เพิ่มคำขอลา
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">{{ $requests->links() }}</div>
</div>
@endsection
