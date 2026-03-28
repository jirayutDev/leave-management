@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-speedometer2"></i>Dashboard</h1>
    <span class="text-muted small">{{ now()->locale('th')->isoFormat('D MMMM YYYY') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:#eff6ff;">
                <i class="bi bi-people" style="color:#3b82f6;"></i>
            </div>
            <div>
                <div class="stat-label">พนักงานทั้งหมด</div>
                <div class="stat-value">{{ $totalEmployees }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:#fef2f2;">
                <i class="bi bi-person-x" style="color:#dc2626;"></i>
            </div>
            <div>
                <div class="stat-label">ลาวันนี้</div>
                <div class="stat-value">{{ $todayLeaves }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:#f0fdf4;">
                <i class="bi bi-calendar-check" style="color:#16a34a;"></i>
            </div>
            <div>
                <div class="stat-label">ลาเดือนนี้</div>
                <div class="stat-value">{{ $monthLeaves }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon" style="background:#fffbeb;">
                <i class="bi bi-sun" style="color:#d97706;"></i>
            </div>
            <div>
                <div class="stat-label">ลาพักร้อน (V)</div>
                <div class="stat-value">{{ $leaveByType['V'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header-bar">
                <span class="fw-semibold" style="font-size:.9rem;"><i class="bi bi-bar-chart me-2 text-primary"></i>สรุปการลาเดือนนี้</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('leave.index') }}" class="btn btn-sm btn-outline-secondary">ตาราง</a>
                    <a href="{{ route('export.form') }}" class="btn btn-sm btn-primary"><i class="bi bi-download me-1"></i>ส่งออก</a>
                </div>
            </div>
            <div class="p-3">
                @php $types = ['V'=>'Vacation','S'=>'Sick','P'=>'Personal','D'=>'Disability','U'=>'Unpaid']; @endphp
                <div class="d-flex flex-column gap-3">
                    @foreach($types as $t => $label)
                    @php $cnt = $leaveByType[$t] ?? 0; $pct = $monthLeaves > 0 ? round($cnt/$monthLeaves*100) : 0; @endphp
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-medium"><span class="badge badge-{{ $t }} me-1">{{ $t }}</span>{{ $label }}</span>
                            <span class="small fw-semibold text-muted">{{ $cnt }} วัน</span>
                        </div>
                        <div class="progress" style="height:5px; border-radius:99px; background:#f1f5f9;">
                            <div class="progress-bar badge-{{ $t }}" style="width:{{ $pct }}%; border-radius:99px;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header-bar">
                <span class="fw-semibold" style="font-size:.9rem;"><i class="bi bi-clock-history me-2 text-primary"></i>การลาล่าสุด</span>
                <a href="{{ route('leave.index') }}" class="btn btn-sm btn-outline-secondary">ดูทั้งหมด</a>
            </div>
            @if($recentLeaves->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                    <p class="text-muted small mb-3">ไม่มีข้อมูลการลาเดือนนี้</p>
                    <a href="{{ route('import.form') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-upload me-1"></i>นำเข้า CSV
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>พนักงาน</th>
                                <th>วันที่</th>
                                <th>ประเภท</th>
                                <th class="d-none d-md-table-cell">หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($recentLeaves as $r)
                            <tr>
                                <td>
                                    <a href="{{ route('employees.show', $r->employee) }}" class="text-decoration-none fw-semibold text-dark">
                                        {{ $r->employee->name }}
                                    </a>
                                </td>
                                <td class="text-nowrap text-muted">{{ $r->leave_date->format('d/m/Y') }}</td>
                                <td><span class="badge badge-{{ $r->leave_type }}">{{ $r->leave_type }}</span></td>
                                <td class="d-none d-md-table-cell text-muted">{{ $r->note ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
