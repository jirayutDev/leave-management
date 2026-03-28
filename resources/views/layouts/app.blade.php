<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Leave') — E-Leave</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 230px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,.07);
            --sidebar-active: #3b82f6;
            --accent: #3b82f6;
            --accent-dark: #1d4ed8;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --card-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.05);
            --card-radius: 12px;
            --text-muted: #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--body-bg); color: #1e293b; font-size: .9rem; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .25s cubic-bezier(.4,0,.2,1);
        }
        .sidebar-brand {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            padding: 1.1rem 1.3rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            gap: .6rem;
            letter-spacing: -.01em;
        }
        .sidebar-brand .brand-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .sidebar-section {
            color: #475569;
            font-size: .67rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 1rem 1.3rem .3rem;
        }
        .sidebar nav { padding: .5rem .75rem 2rem; flex: 1; overflow-y: auto; }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: .5rem .85rem;
            border-radius: 8px;
            margin-bottom: 2px;
            font-size: .875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: background .15s, color .15s;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 18px; flex-shrink: 0; }
        .sidebar .nav-link:hover { background: var(--sidebar-hover); color: #e2e8f0; }
        .sidebar .nav-link.active { background: var(--accent); color: #fff; font-weight: 600; }

        /* ── Topbar (mobile) ── */
        .topbar {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: var(--sidebar-bg);
            z-index: 1039;
            align-items: center;
            padding: 0 1rem;
            gap: .75rem;
            color: #fff;
        }
        .topbar-brand { font-weight: 700; font-size: .95rem; flex: 1; }

        /* ── Backdrop ── */
        .sidebar-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1039;
            backdrop-filter: blur(2px);
        }

        /* ── Main ── */
        .main-wrap { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin .25s; }
        .main-content { padding: 1.75rem 2rem; }

        /* ── Cards ── */
        .card {
            background: var(--card-bg);
            border: 1px solid rgba(0,0,0,.05);
            box-shadow: var(--card-shadow);
            border-radius: var(--card-radius);
        }
        .card-header-bar {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        /* ── Page heading ── */
        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .page-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .page-title i { color: var(--accent); }

        /* ── Stat card ── */
        .stat-card { border-radius: var(--card-radius); padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }
        .stat-label { font-size: .78rem; color: var(--text-muted); font-weight: 500; }
        .stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; color: #0f172a; }

        /* ── Tables ── */
        .table { font-size: .875rem; }
        .table thead th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); border-bottom: 1px solid #e2e8f0; background: #f8fafc; padding: .65rem 1rem; }
        .table > :not(caption) > * > * { vertical-align: middle; padding: .75rem 1rem; }
        .table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .1s; }
        .table tbody tr:last-child { border-bottom: none; }
        .table-hover tbody tr:hover { background: #f8fafc; }

        /* ── Badges ── */
        .badge { font-weight: 600; letter-spacing: .01em; }
        .badge-V { background: #16a34a !important; }
        .badge-S { background: #dc2626 !important; }
        .badge-P { background: #2563eb !important; }
        .badge-D { background: #64748b !important; }
        .badge-U { background: #ea580c !important; }

        /* ── Buttons ── */
        .btn { font-weight: 500; border-radius: 8px; font-size: .875rem; }
        .btn-sm { font-size: .8rem; padding: .3rem .75rem; }
        .btn-primary { background: var(--accent); border-color: var(--accent); }
        .btn-primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }

        /* ── Forms ── */
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #e2e8f0;
            font-size: .875rem;
            color: #1e293b;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        .form-label { font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }

        /* ── Pagination ── */
        .pagination { gap: 3px; margin: 0; }
        .page-link { border-radius: 8px !important; font-size: .8rem; padding: .35rem .65rem; line-height: 1.4; border-color: #e2e8f0; color: #475569; }
        .page-link i { font-size: .75rem; vertical-align: middle; line-height: 1; }
        .page-item.active .page-link { background: var(--accent); border-color: var(--accent); color: #fff; }
        .page-item.disabled .page-link { background: #f8fafc; color: #cbd5e1; }

        /* ── Filter bar ── */
        .filter-bar { background: var(--card-bg); border: 1px solid rgba(0,0,0,.05); box-shadow: var(--card-shadow); border-radius: var(--card-radius); padding: 1rem 1.25rem; margin-bottom: 1.25rem; }

        /* ── Sticky col (leave table) ── */
        .sticky-col { position: sticky; left: 0; background: #fff; z-index: 2; border-right: 2px solid #e2e8f0; }
        thead .sticky-col { background: #f8fafc; z-index: 3; }

        /* ── Divider ── */
        .section-divider { height: 1px; background: #f1f5f9; margin: 1.25rem 0; }

        /* ── Alerts ── */
        .alert { border-radius: 10px; border: none; font-size: .875rem; }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .topbar { display: flex; }
            .main-wrap { margin-left: 0; padding-top: 56px; }
            .main-content { padding: 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="topbar" id="topbar">
    <button class="btn btn-link text-white p-0" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    <span class="topbar-brand"><i class="bi bi-calendar2-check me-1 text-primary"></i>E-Leave</span>
    <span class="text-white-50 small">@yield('title')</span>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-calendar2-check text-white"></i></div>
        E-Leave
        <button class="btn btn-link text-white ms-auto p-0 d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>Dashboard
        </a>

        <div class="sidebar-section">วันลา</div>
        <a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>คำขอลา
        </a>
        <a href="{{ route('leave.index') }}" class="nav-link {{ request()->routeIs('leave.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>ตารางวันลา
        </a>
        <a href="{{ route('import.form') }}" class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i>นำเข้า CSV
        </a>
        <a href="{{ route('export.form') }}" class="nav-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
            <i class="bi bi-download"></i>ส่งออกใบลา
        </a>

        <div class="sidebar-section">ระบบ</div>
        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>พนักงาน
        </a>
        <a href="{{ route('admins.index') }}" class="nav-link {{ request()->routeIs('admins.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock"></i>จัดการ Admin
        </a>
    </nav>

    <div style="padding:.75rem;border-top:1px solid rgba(255,255,255,.07);">
        <div class="d-flex align-items-center gap-2 px-2 py-1 mb-2" style="color:#94a3b8;font-size:.8rem;">
            <i class="bi bi-person-circle" style="font-size:1rem;"></i>
            <span class="text-truncate">{{ auth()->user()?->name }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn w-100 text-start d-flex align-items-center gap-2"
                style="background:rgba(255,255,255,.06);color:#94a3b8;font-size:.875rem;font-weight:500;border-radius:8px;padding:.5rem .85rem;border:none;">
                <i class="bi bi-box-arrow-right" style="font-size:1rem;width:18px;flex-shrink:0;"></i>
                ออกจากระบบ
            </button>
        </form>
    </div>
</aside>

<div class="main-wrap" id="mainWrap">
    <div class="main-content">
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: @json(session('success')), timer: 2500, showConfirmButton: false, toast: true, position: 'top-end' });
                });
            </script>
        @endif
        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: @json(session('error')), confirmButtonColor: '#3b82f6' });
                });
            </script>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm]');
        if (!btn) return;

        e.preventDefault();
        const msg  = btn.dataset.confirm || 'ยืนยันการดำเนินการ?';
        const icon = btn.dataset.confirmIcon || 'warning';
        const form = btn.closest('form');

        Swal.fire({
            title: 'ยืนยัน?',
            text: msg,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            borderRadius: '12px',
        }).then(result => {
            if (result.isConfirmed && form) form.submit();
        });
    });
});
</script>
<script>
    const sidebar   = document.getElementById('sidebar');
    const backdrop  = document.getElementById('sidebarBackdrop');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn  = document.getElementById('sidebarClose');

    function openSidebar()  { sidebar.classList.add('show'); backdrop.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('show'); backdrop.classList.remove('show'); }

    toggleBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);
</script>
@stack('scripts')
</body>
</html>
