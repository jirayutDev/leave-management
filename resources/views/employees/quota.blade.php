@extends('layouts.app')
@section('title', 'โควต้าวันลา')
@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">โควต้าวันลา: {{ $employee->name }}</h5>
</div>

<div class="card p-3 p-md-4" style="max-width:540px;">
    <form method="POST" action="{{ route('employees.quota.update', $employee) }}">
        @csrf @method('PUT')

        <div class="mb-4 d-flex align-items-center gap-3">
            <label class="form-label mb-0 fw-semibold">ปี</label>
            <select name="year" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="year" value="{{ $year }}">

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ประเภท</th>
                        <th class="text-center" style="width:90px;">โควต้า</th>
                        <th class="text-center" style="width:70px;">ใช้แล้ว</th>
                        <th class="text-center" style="width:70px;">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $fields = ['vacation'=>'V','sick'=>'S','personal'=>'P','disability'=>'D','unpaid'=>'U'];
                    $labels = ['vacation'=>'Vacation','sick'=>'Sick','personal'=>'Personal','disability'=>'Disability','unpaid'=>'Unpaid'];
                @endphp
                @foreach($fields as $field => $type)
                @php
                    $qVal = $quota ? $quota->$field : 0;
                    $used = $employee->usedLeaveInYear($type, $year);
                    $rem  = $qVal - $used;
                @endphp
                <tr>
                    <td>
                        <span class="badge badge-{{ $type }} me-1">{{ $type }}</span>
                        <span class="small">{{ $labels[$field] }}</span>
                    </td>
                    <td class="text-center">
                        <input type="number" name="{{ $field }}" min="0" max="365"
                               class="form-control form-control-sm text-center px-1"
                               value="{{ old($field, $qVal) }}">
                    </td>
                    <td class="text-center small fw-semibold">{{ $used }}</td>
                    <td class="text-center small fw-semibold {{ $rem < 0 ? 'text-danger' : '' }}">{{ $rem }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" name="year" value="{{ $year }}" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>บันทึก
            </button>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
    </form>
</div>
@endsection
