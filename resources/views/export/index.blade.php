@extends('layouts.app')
@section('title', 'ส่งออกใบลา')
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0"><i class="bi bi-download"></i>ส่งออกใบลา</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card p-3 p-md-4">
            <h6 class="fw-bold mb-3">เลือกเดือน/ปีที่ต้องการส่งออก</h6>

            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label small fw-semibold">เดือน</label>
                    <select id="selMonth" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold">ปี</label>
                    <select id="selYear" class="form-select">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="d-grid gap-2">
                <form method="POST" action="{{ route('export.excel') }}" id="formExcel">
                    @csrf
                    <input type="hidden" name="month" id="monthExcel">
                    <input type="hidden" name="year"  id="yearExcel">
                    <button type="submit" class="btn btn-success w-100" onclick="syncFields('Excel')">
                        <i class="bi bi-file-earmark-excel me-2"></i>ส่งออกเป็น Excel (.xlsx)
                    </button>
                </form>

                <form method="POST" action="{{ route('export.csv') }}" id="formCsv">
                    @csrf
                    <input type="hidden" name="month" id="monthCsv">
                    <input type="hidden" name="year"  id="yearCsv">
                    <button type="submit" class="btn btn-outline-primary w-100" onclick="syncFields('Csv')">
                        <i class="bi bi-file-earmark-text me-2"></i>ส่งออกเป็น CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card p-3 p-md-4 bg-light h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1 text-info"></i>รายละเอียดไฟล์ที่ส่งออก</h6>

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="border rounded p-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success"><i class="bi bi-file-earmark-excel"></i></span>
                            <strong class="small">Excel (.xlsx)</strong>
                        </div>
                        <ul class="small mb-0 ps-3 text-muted">
                            <li>รูปแบบ BITNANCE Leave Tracker</li>
                            <li>สีพื้นหลังวันหยุดสุดสัปดาห์</li>
                            <li>ส่วนหัวตารางสีพร้อม Freeze Panes</li>
                            <li>สรุปยอดรวม + โควต้าคงเหลือ</li>
                            <li>นำเข้ากลับได้ทันที</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="border rounded p-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary"><i class="bi bi-file-earmark-text"></i></span>
                            <strong class="small">CSV</strong>
                        </div>
                        <ul class="small mb-0 ps-3 text-muted">
                            <li>รูปแบบเดียวกับไฟล์นำเข้า</li>
                            <li>เปิดได้ทุกโปรแกรม</li>
                            <li>นำเข้ากลับระบบได้</li>
                            <li>ขนาดไฟล์เล็ก</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-3 p-2 border rounded bg-white">
                <p class="small fw-semibold mb-1 text-muted">โครงสร้างไฟล์:</p>
                <code class="small" style="font-size:.72rem; white-space:pre-wrap;">Row 1: ชื่อเดือนและปี
Row 2: ว่าง
Row 3: ป้ายกำกับประเภทการลา
Row 4: วัน (Sun, Mon, ...)
Row 5: หัวคอลัมน์ (ID, Name, 1–31, V,S,P,D,U, โควต้า)
Row 6+: ข้อมูลพนักงานรายคน
Last: ยอดรวม</code>
            </div>
        </div>
    </div>
</div>

<div class="card p-3 mt-4" id="previewCard">
    <h6 class="fw-bold mb-3"><i class="bi bi-eye me-1 text-primary"></i>ตัวอย่างข้อมูลที่จะส่งออก</h6>
    <div class="row g-3 text-center">
        <div class="col-6 col-sm-3">
            <div class="border rounded p-3">
                <div class="fs-3 fw-bold text-primary">{{ $totalEmployees ?? 0 }}</div>
                <div class="text-muted small">พนักงาน</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="border rounded p-3">
                <div class="fs-3 fw-bold badge-V text-white rounded-2 px-2">{{ $totalLeaveThisMonth ?? 0 }}</div>
                <div class="text-muted small mt-1">รายการลา</div>
            </div>
        </div>
        @foreach(['V'=>'Vacation','S'=>'Sick','P'=>'Personal'] as $t=>$v)
        <div class="col-4 col-sm-2">
            <div class="border rounded p-2">
                <span class="badge badge-{{ $t }} mb-1">{{ $t }}</span>
                <div class="fw-bold small">{{ $leaveCounts[$t] ?? 0 }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ $v }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function syncFields(type) {
    document.getElementById('month' + type).value = document.getElementById('selMonth').value;
    document.getElementById('year'  + type).value = document.getElementById('selYear').value;
}
</script>
@endpush
@endsection
