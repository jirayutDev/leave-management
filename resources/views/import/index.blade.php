@extends('layouts.app')
@section('title', 'นำเข้า CSV')
@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold"><i class="bi bi-upload me-2 text-primary"></i>นำเข้าข้อมูลวันลาจาก CSV</h5>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-3">เลือกไฟล์ CSV</h6>
            <form method="POST" action="{{ route('import.process') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small">ไฟล์ CSV รูปแบบ BITNANCE Leave Tracker</label>
                    <input type="file" name="csv_file"
                           class="form-control @error('csv_file') is-invalid @enderror"
                           accept=".csv,.txt" required>
                    @error('csv_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i>นำเข้าข้อมูล
                </button>
            </form>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card p-3 p-md-4 bg-light h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1 text-info"></i>รูปแบบไฟล์</h6>
            <ul class="small mb-3 ps-3">
                <li>Row 1: ชื่อเดือนและปี เช่น <code>December 2023</code></li>
                <li>Row 4: หัวข้อคอลัมน์ (ID, Name, 1–31, Totals, Quota)</li>
                <li>Row 5+: ข้อมูลพนักงาน</li>
            </ul>
            <div class="d-flex gap-2 flex-wrap mb-2">
                @foreach(['V'=>'Vacation','S'=>'Sick','P'=>'Personal','D'=>'Disability','U'=>'Unpaid'] as $k=>$v)
                    <span class="small"><span class="badge badge-{{ $k }}">{{ $k }}</span> {{ $v }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

@if(session('importResult'))
@php $result = session('importResult'); @endphp
<div class="card p-3 p-md-4 mt-4 border-success">
    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle me-1"></i>ผลการนำเข้า</h6>
    <dl class="row mb-3">
        <dt class="col-sm-4">เดือน/ปี</dt>    <dd class="col-sm-8">{{ $result['month'] }}/{{ $result['year'] }}</dd>
        <dt class="col-sm-4">นำเข้าสำเร็จ</dt><dd class="col-sm-8 text-success fw-bold">{{ $result['imported'] }} รายการ</dd>
    </dl>
    <a href="{{ route('leave.index', ['month'=>$result['month'],'year'=>$result['year']]) }}" class="btn btn-sm btn-primary">
        <i class="bi bi-calendar3 me-1"></i>ดูตารางวันลา
    </a>
</div>
@endif
@endsection
