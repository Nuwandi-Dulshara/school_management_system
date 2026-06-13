@extends('layouts.app')

@section('title', ($pageTitle ?? 'Super Admin') . ' - School Management System')

@section('styles')
<style>
    .dashboard-wrapper { display: flex; min-height: 100vh; }
    .sidebar {
        width: 260px; background: linear-gradient(135deg, var(--navy-secondary) 0%, #11224e 100%);
        color: white; padding: 2rem 0 7rem; position: fixed; inset: 0 auto 0 0;
        overflow-y: auto; box-shadow: 2px 0 10px rgba(0, 0, 0, .1); z-index: 1000;
    }
    .sidebar-brand { padding: 0 1.5rem 2rem; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 1.25rem; text-align: center; }
    .sidebar-brand i { font-size: 2.5rem; color: var(--amber-accent); display: block; margin-bottom: .5rem; }
    .sidebar-brand h4 { font-size: 1.3rem; font-weight: 700; margin: 0; color: white; }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu-link {
        display: flex; align-items: center; padding: .875rem 1.5rem; color: rgba(255,255,255,.72);
        text-decoration: none; transition: all .2s ease; border-left: 3px solid transparent;
    }
    .sidebar-menu-toggle {
        width: 100%; background: transparent; border-top: 0; border-right: 0; border-bottom: 0;
        text-align: left; cursor: pointer;
    }
    .sidebar-menu-link:hover, .sidebar-menu-link.active {
        color: white; background-color: rgba(255,255,255,.12); border-left-color: var(--amber-accent);
    }
    .sidebar-menu-link:focus-visible {
        color: white; outline: 2px solid var(--amber-accent); outline-offset: -2px;
    }
    .sidebar-menu-link.active { font-weight: 600; }
    .sidebar-menu-link i { margin-right: .75rem; font-size: 1.1rem; }
    .sidebar-menu-group { margin-top: .25rem; }
    .sidebar-menu-toggle .sidebar-menu-arrow {
        margin: 0 0 0 auto; font-size: .8rem; transition: transform .2s ease;
    }
    .sidebar-menu-group.is-open .sidebar-menu-arrow { transform: rotate(180deg); }
    .sidebar-submenu { display: none; background: rgba(0,0,0,.08); }
    .sidebar-menu-group.is-open .sidebar-submenu { display: block; }
    .sidebar-submenu .sidebar-menu-link { padding-left: 3.2rem; font-size: .92rem; }
    .sidebar-submenu .sidebar-menu-link i { font-size: .55rem; }
    .sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; border-top: 1px solid rgba(255,255,255,.1); background: rgba(0,0,0,.2); }
    .sidebar-footer .btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: .5rem; }
    .main-content { margin-left: 260px; flex: 1; padding: 2rem; min-width: 0; min-height: 100vh; }
    .topbar {
        background: white; padding: 1rem 2rem; margin: -2rem -2rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,.05);
        display: flex; justify-content: space-between; align-items: center;
    }
    .topbar-title { font-size: 1.5rem; font-weight: 700; color: var(--navy-secondary); margin: 0; }
    .topbar-user { display: flex; align-items: center; gap: .75rem; color: var(--navy-secondary); }
    .topbar-user i { font-size: 1.5rem; }
    .content-area { background: white; border-radius: 10px; padding: 2rem; min-height: 60vh; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
    .btn-academic { background: var(--navy-secondary); color: white; border-color: var(--navy-secondary); }
    .btn-academic:hover { background: var(--navy-hover); color: white; border-color: var(--navy-hover); }
    .text-navy { color: var(--navy-secondary) !important; }
    .page-heading { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .page-heading h1 { color: var(--navy-secondary); font-size: 1.55rem; font-weight: 700; margin: 0; }
    .page-heading p { color: #6b7280; margin: .35rem 0 0; }
    .form-control:focus, .form-select:focus { border-color: var(--navy-secondary); box-shadow: 0 0 0 .25rem rgba(30,58,138,.12); }
    .table thead th { color: #4b5563; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; background: #f8fafc; border-bottom-width: 1px; white-space: nowrap; }
    .table td { vertical-align: middle; }
    .user-avatar { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #e8edfb; color: var(--navy-secondary); font-weight: 700; }
    .status-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .65rem; border-radius: 999px; font-size: .78rem; font-weight: 600; }
    .status-active { color: #166534; background: #dcfce7; }
    .status-inactive { color: #991b1b; background: #fee2e2; }
    .status-transferred { color: #92400e; background: #fef3c7; }
    .status-graduated { color: #5b21b6; background: #ede9fe; }
    .role-badge { color: var(--navy-secondary); background: #e8edfb; border-radius: 999px; padding: .3rem .65rem; font-size: .78rem; font-weight: 600; }
    .empty-state { text-align: center; padding: 4rem 1rem; color: #6b7280; }
    .empty-state i { font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 1rem; }
    .detail-label { color: #6b7280; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .35rem; }
    .detail-value { color: #1f2937; font-weight: 600; margin: 0; }
    .role-card { border: 1px solid #e5e7eb; border-radius: 10px; height: 100%; padding: 1.5rem; transition: transform .2s ease, box-shadow .2s ease; }
    .role-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(30,58,138,.09); }
    .role-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #e8edfb; color: var(--navy-secondary); font-size: 1.4rem; }
    @media (max-width: 768px) {
        .dashboard-wrapper { flex-direction: column; }
        .sidebar { width: 100%; height: auto; position: relative; padding: 1rem 0; }
        .sidebar-brand { padding-bottom: 1rem; margin-bottom: .5rem; }
        .sidebar-footer { position: static; margin-top: 1rem; }
        .main-content { margin-left: 0; padding: 1rem; }
        .topbar { margin: -1rem -1rem 1rem; padding: 1rem; }
        .content-area { padding: 1.25rem; }
        .page-heading { align-items: flex-start; flex-direction: column; }
    }
</style>
@yield('page_styles')
@endsection

@section('content')
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <h4>EduPulse</h4>
        </div>

        @php
            $userMenuActive = request()->routeIs('super_admin.users.*', 'super_admin.roles.*');
            $studentMenuActive = request()->routeIs('super_admin.students.*');
            $teacherMenuActive = request()->routeIs('super_admin.teachers.*');
        @endphp

        <nav aria-label="Super Admin navigation">
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('dashboard.super_admin') }}" class="sidebar-menu-link {{ request()->routeIs('dashboard.super_admin') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-group {{ $userMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $userMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $userMenuActive ? 'true' : 'false' }}" aria-controls="user-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-people-fill"></i>
                    <span>User Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="user-management-menu">
                    <li>
                        <a href="{{ route('super_admin.users.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.users.index', 'super_admin.users.show', 'super_admin.users.edit') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>User List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.users.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.users.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.roles.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.roles.*') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Role Management</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $studentMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $studentMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $studentMenuActive ? 'true' : 'false' }}" aria-controls="student-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Student Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="student-management-menu">
                    <li>
                        <a href="{{ route('super_admin.students.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.students.index', 'super_admin.students.show', 'super_admin.students.edit', 'super_admin.students.guardians*', 'super_admin.students.documents*') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Student List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.students.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.students.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add Student</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.students.status') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.students.status*') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Student Status</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $teacherMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $teacherMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $teacherMenuActive ? 'true' : 'false' }}" aria-controls="teacher-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-person-video3"></i>
                    <span>Teacher Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="teacher-management-menu">
                    <li>
                        <a href="{{ route('super_admin.teachers.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teachers.index', 'super_admin.teachers.show', 'super_admin.teachers.edit') && !request('manage') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Teacher List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teachers.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teachers.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add Teacher</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teachers.index', ['manage' => 'classes']) }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teachers.classes*') || request('manage') === 'classes' ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Assigned Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teachers.index', ['manage' => 'subjects']) }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teachers.subjects*') || request('manage') === 'subjects' ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Assigned Subjects</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h2 class="topbar-title">{{ $pageTitle ?? 'Dashboard' }}</h2>
            <div class="topbar-user">
                <i class="bi bi-person-circle"></i>
                <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="content-area">
            @yield('super_admin_content')
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const group = toggle.closest('[data-sidebar-group]');
            const isOpen = group.classList.toggle('is-open');

            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
</script>
@endsection
