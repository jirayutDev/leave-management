@extends('layouts.app')
@section('title', 'แก้ไขพนักงาน')
@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm btn-icon"><i class="bi bi-arrow-left"></i></a>
        <h1 class="page-title mb-0">แก้ไข: {{ $employee->name }}</h1>
    </div>
</div>

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <label class="form-label">รหัสพนักงาน <span class="text-danger">*</span></label>
                <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror"
                       value="{{ old('employee_code', $employee->employee_code) }}" required>
                @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $employee->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $employee->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">แผนก</label>
                <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}">
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">ตำแหน่ง</label>
                <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}">
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">วันที่เริ่มงาน</label>
                <input type="date" name="hire_date" class="form-control"
                       value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">สถานะ</label>
                <select name="status" class="form-select">
                    <option value="active"   {{ old('status',$employee->status)==='active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status',$employee->status)==='inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
