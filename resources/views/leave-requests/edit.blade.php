@extends('layouts.app')
@section('title', 'แก้ไขคำขอลา')
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0">แก้ไขคำขอลา</h1>
    </div>
</div>

<div class="col-12 col-lg-7">
    <div class="card p-3 p-md-4">
        <form method="POST" action="{{ route('leave-requests.update', $leaveRequest) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">พนักงาน <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                    <option value="">-- เลือกพนักงาน --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id', $leaveRequest->employee_id) == $emp->id ? 'selected' : '' }}>
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
                               value="{{ $k }}" {{ old('leave_type', $leaveRequest->leave_type) == $k ? 'checked' : '' }} required>
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
                           value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="endDate"
                           class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <div class="alert alert-info py-2 px-3 d-flex align-items-center gap-3 mb-0">
                    <i class="bi bi-calendar2-check text-info fs-5"></i>
                    <span class="small">จำนวนวันลา (วันทำงาน):
                        <strong id="daysCount" class="fs-6 text-primary ms-1">{{ $leaveRequest->days }}</strong> วัน
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">สาเหตุการลา <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4"
                          class="form-control @error('reason') is-invalid @enderror"
                          placeholder="ระบุสาเหตุการลา..." required minlength="5">{{ old('reason', $leaveRequest->reason) }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">ไฟล์แนบ</label>
                @if($leaveRequest->attachment)
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <a href="{{ Storage::url($leaveRequest->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-paperclip me-1"></i>ดูไฟล์เดิม
                        </a>
                        <span class="text-muted small">อัปโหลดใหม่เพื่อแทนที่</span>
                    </div>
                @endif
                <input type="file" name="attachment" class="form-control form-control-sm @error('attachment') is-invalid @enderror"
                       accept=".pdf,.jpg,.jpeg,.png">
                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
                <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const startEl = document.getElementById('startDate');
const endEl   = document.getElementById('endDate');
const daysEl  = document.getElementById('daysCount');

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
</script>
@endpush
@endsection
