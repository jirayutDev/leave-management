@extends('layouts.app')

@section('title', 'จัดการ Admin')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="bi bi-shield-lock"></i> จัดการ Admin</h1>
    <a href="{{ route('admins.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> เพิ่ม Admin
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>วันที่สร้าง</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td class="text-muted">{{ $admin->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;color:#475569;flex-shrink:0;">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            {{ $admin->name }}
                            @if($admin->id === auth()->id())
                                <span class="badge bg-primary" style="font-size:.7rem;">คุณ</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td class="text-muted">{{ $admin->created_at->format('d/m/Y') }}</td>
                    <td class="text-end">
                        @if($admin->id !== auth()->id())
                        <form method="POST" action="{{ route('admins.destroy', $admin) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                data-confirm="ต้องการลบ admin '{{ $admin->name }}' ใช่ไหม?"
                                data-confirm-icon="warning">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูล</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
    <div class="d-flex justify-content-end p-3">
        {{ $admins->links() }}
    </div>
    @endif
</div>
@endsection
