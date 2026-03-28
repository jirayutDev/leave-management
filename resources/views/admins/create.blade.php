@extends('layouts.app')

@section('title', 'เพิ่ม Admin')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="bi bi-person-plus"></i> เพิ่ม Admin</h1>
    <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> กลับ
    </a>
</div>

<div class="card" style="max-width:520px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admins.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>บันทึก</button>
                <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
