@extends('layouts.app')
@section('title', 'ยื่นคำขอลา')
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0"><i class="bi bi-file-earmark-plus"></i>ยื่นคำขอลา</h1>
    </div>
</div>

<div class="row g-4 align-items-start">
    <div class="col-12 col-lg-7">
        <div class="card p-3 p-md-4">
            <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data" id="leaveForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">พนักงาน <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employeeSelect"
                            class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">-- เลือกพนักงาน --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                    data-quota="{{ json_encode($emp->quotaForYear(now()->year) ?? []) }}"
                                    {{ ($preselect == $emp->id || old('employee_id') == $emp->id) ? 'selected' : '' }}>
                                {{ $emp->employee_code }} — {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">ประเภทการลา <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        @foreach(\App\Models\LeaveRecord::$typeLabels as $k => $v)
                        <div class="col-6 col-sm-4">
                            <input type="radio" class="btn-check" name="leave_type" id="type_{{ $k }}"
                                   value="{{ $k }}" {{ old('leave_type') == $k ? 'checked' : '' }} required>
                            <label class="btn btn-outline-secondary w-100 text-start" for="type_{{ $k }}">
                                <span class="badge badge-{{ $k }} me-1">{{ $k }}</span> {{ $v }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('leave_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">วันที่เริ่มลา <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="startDate"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="endDate"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', now()->format('Y-m-d')) }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="alert alert-info py-2 px-3 d-flex align-items-center gap-3 mb-0">
                        <i class="bi bi-calendar2-check text-info fs-5"></i>
                        <span class="small">จำนวนวันลา (วันทำงาน):
                            <strong id="daysCount" class="fs-6 text-primary ms-1">—</strong> วัน
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">สาเหตุการลา <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="4"
                              class="form-control @error('reason') is-invalid @enderror"
                              placeholder="ระบุสาเหตุการลาอย่างละเอียด เช่น ป่วยเป็นไข้หวัด, ธุระสำคัญ, พักผ่อนประจำปี ..."
                              required minlength="5">{{ old('reason') }}</textarea>
                    <div class="form-text text-end">
                        <span id="reasonCount">0</span>/1000
                    </div>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">ไฟล์แนบ <span class="text-muted fw-normal">(ไม่บังคับ)</span></label>
                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">รองรับ PDF, JPG, PNG ขนาดสูงสุด 5 MB</div>
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>ยื่นคำขอลา
                    </button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card p-3" id="quotaCard">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>โควต้าวันลาปี {{ now()->year }}</h6>
            <div id="quotaBody">
                <p class="text-muted small">เลือกพนักงานเพื่อดูโควต้า</p>
            </div>
        </div>

        <div class="card p-3 mt-3 bg-light">
            <h6 class="fw-bold mb-2 small"><i class="bi bi-info-circle me-1 text-info"></i>หมายเหตุ</h6>
            <ul class="small mb-0 ps-3 text-muted">
                <li>ระบบนับเฉพาะ <strong>วันทำงาน</strong> (จันทร์–ศุกร์)</li>
                <li>วันเสาร์–อาทิตย์ไม่นับเป็นวันลา</li>
                <li>แนบใบรับรองแพทย์สำหรับลาป่วย (S) ≥ 3 วัน</li>
                <li>คำขอรอการอนุมัติจากผู้บริหาร</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
const startEl   = document.getElementById('startDate');
const endEl     = document.getElementById('endDate');
const daysEl    = document.getElementById('daysCount');
const reasonEl  = document.querySelector('textarea[name=reason]');
const reasonCnt = document.getElementById('reasonCount');
const empSel    = document.getElementById('employeeSelect');

reasonEl.addEventListener('input', () => {
    reasonCnt.textContent = reasonEl.value.length;
});
reasonCnt.textContent = reasonEl.value.length;

async function calcDays() {
    const s = startEl.value, e = endEl.value;
    if (!s || !e || s > e) { daysEl.textContent = '—'; return; }
    const res  = await fetch('{{ route('leave-requests.calc-days') }}?start_date=' + s + '&end_date=' + e,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    daysEl.textContent = data.days;
}

startEl.addEventListener('change', calcDays);
endEl.addEventListener('change', calcDays);
calcDays();

const quotaLabels = {V:'Vacation',S:'Sick',P:'Personal',D:'Disability',U:'Unpaid'};
const quotaFields = {V:'vacation',S:'sick',P:'personal',D:'disability',U:'unpaid'};
const quotaColors = {V:'198754',S:'dc3545',P:'0d6efd',D:'6c757d',U:'fd7e14'};

empSel.addEventListener('change', function() {
    const opt   = this.options[this.selectedIndex];
    const quota = opt.dataset.quota ? JSON.parse(opt.dataset.quota) : null;
    const body  = document.getElementById('quotaBody');

    if (!quota || !quota.id) {
        body.innerHTML = '<p class="text-muted small">ไม่มีข้อมูลโควต้าสำหรับพนักงานคนนี้</p>';
        return;
    }

    let html = '<div class="row g-2">';
    Object.entries(quotaFields).forEach(([type, field]) => {
        const q = quota[field] ?? 0;
        html += `
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small">
                    <span class="badge me-1" style="background:#${quotaColors[type]}">${type}</span>
                    ${quotaLabels[type]}
                </span>
                <span class="small fw-bold">${q} วัน</span>
            </div>
            <div class="progress" style="height:5px;">
                <div class="progress-bar" style="width:100%; background:#${quotaColors[type]};"></div>
            </div>
        </div>`;
    });
    html += '</div>';
    body.innerHTML = html;
});

if (empSel.value) empSel.dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
