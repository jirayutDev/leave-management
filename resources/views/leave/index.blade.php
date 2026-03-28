@extends('layouts.app')
@section('title', 'ตารางวันลา')
@section('content')

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-calendar3"></i>ตารางวันลา</h1>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
        <a href="{{ route('import.form') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-upload me-1"></i><span class="d-none d-sm-inline">นำเข้า</span> CSV
        </a>
    </div>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap" style="font-size:.8rem;">
    @foreach(['V'=>'Vacation','S'=>'Sick','P'=>'Personal','D'=>'Disability','U'=>'Unpaid'] as $k=>$v)
        <span class="d-flex align-items-center gap-1">
            <span class="badge badge-{{ $k }}">{{ $k }}</span>
            <span class="text-muted d-none d-sm-inline">{{ $v }}</span>
        </span>
    @endforeach
</div>

<div class="card">
    <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
        <table class="table table-bordered mb-0" style="font-size:.78rem; white-space:nowrap;">
            <thead>
                <tr>
                    <th class="sticky-col" style="min-width:160px;">พนักงาน</th>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php $date = \Carbon\Carbon::create($year, $month, $d); @endphp
                        <th class="text-center p-1 {{ in_array($date->dayOfWeek,[0,6]) ? 'table-secondary' : '' }}"
                            style="min-width:30px; width:30px;">
                            <div class="fw-semibold">{{ $d }}</div>
                            <div class="text-muted" style="font-size:.6rem;">{{ $date->format('D') }}</div>
                        </th>
                    @endfor
                    @foreach(['V','S','P','D','U'] as $t)
                        <th class="text-center p-1" style="min-width:30px; width:30px;">
                            <span class="badge badge-{{ $t }}" style="font-size:.6rem;">{{ $t }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($employees as $emp)
                @php
                    $empLeaves = $emp->leaveRecords->keyBy(fn($r) => $r->leave_date->day);
                    $quota     = $emp->leaveQuotas->first();
                    $fields    = ['V'=>'vacation','S'=>'sick','P'=>'personal','D'=>'disability','U'=>'unpaid'];
                @endphp
                <tr>
                    <td class="sticky-col py-2">
                        <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none fw-semibold text-dark d-block lh-sm">
                            {{ $emp->name }}
                        </a>
                        <span class="text-muted" style="font-size:.68rem;">{{ $emp->employee_code }}</span>
                    </td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $date      = \Carbon\Carbon::create($year, $month, $d);
                            $rec       = $empLeaves->get($d);
                            $isWeekend = in_array($date->dayOfWeek, [0, 6]);
                        @endphp
                        <td class="text-center p-0 {{ $isWeekend ? 'table-secondary' : '' }}" style="height:36px;">
                            @if($rec)
                                <span class="badge badge-{{ $rec->leave_type }}" style="font-size:.6rem;">{{ $rec->leave_type }}</span>
                            @elseif(!$isWeekend)
                                <span class="text-muted" style="opacity:.3;">·</span>
                            @endif
                        </td>
                    @endfor
                    @foreach(array_keys($fields) as $t)
                        @php
                            $q    = $quota ? $quota->{$fields[$t]} : 0;
                            $used = $emp->leaveRecords->where('leave_type', $t)->count();
                            $rem  = $q - $used;
                        @endphp
                        <td class="text-center fw-semibold {{ $rem < 0 ? 'text-danger' : 'text-muted' }}" style="font-size:.75rem;">
                            {{ $rem }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
