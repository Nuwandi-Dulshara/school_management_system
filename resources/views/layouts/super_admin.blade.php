@extends('layouts.app')

@section('title', ($pageTitle ?? 'Dashboard') . ' - School Management System')

@section('styles')
<style>
    .dashboard-wrapper { display: flex; min-height: 100vh; }
    .sidebar {
        width: 280px; background: linear-gradient(180deg, var(--navy-secondary) 0%, #11224e 100%);
        height: 100vh; color: white; padding: 0; position: fixed; inset: 0 auto 0 0;
        display: flex; flex-direction: column; overflow: hidden;
        box-shadow: 4px 0 18px rgba(15, 23, 42, .14); z-index: 1040;
        transition: transform .25s ease;
    }
    .sidebar-brand {
        flex: 0 0 auto; min-height: 82px; padding: 1.25rem 1.4rem;
        border-bottom: 1px solid rgba(255,255,255,.1);
        display: flex; align-items: center; gap: .85rem;
    }
    .sidebar-brand i { font-size: 2rem; color: var(--amber-accent); }
    .sidebar-brand h4 { font-size: 1.25rem; font-weight: 700; margin: 0; color: white; letter-spacing: .01em; }
    .sidebar-brand small { display: block; color: rgba(255,255,255,.58); font-size: .72rem; margin-top: .1rem; }
    .sidebar nav {
        flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 1rem .75rem 1.5rem;
        scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.22) transparent;
    }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu-link {
        min-height: 46px; display: flex; align-items: center; gap: .8rem; padding: .72rem .9rem;
        color: rgba(255,255,255,.72); border-radius: 8px; text-decoration: none;
        transition: color .2s ease, background-color .2s ease, transform .2s ease;
        border: 0;
    }
    .sidebar-menu-toggle {
        width: 100%; background: transparent; text-align: left; cursor: pointer;
    }
    .sidebar-menu-link:hover, .sidebar-menu-link.active {
        color: white; background-color: rgba(255,255,255,.12);
    }
    .sidebar-menu-link.active { box-shadow: inset 3px 0 0 var(--amber-accent); background-color: rgba(255,255,255,.15); }
    .sidebar-menu-link:hover { transform: translateX(2px); }
    .sidebar-menu-link:focus-visible {
        color: white; outline: 2px solid var(--amber-accent); outline-offset: 1px;
    }
    .sidebar-menu-link.active { font-weight: 600; }
    .sidebar-menu-link > i:first-child { flex: 0 0 20px; margin: 0; font-size: 1.05rem; text-align: center; }
    .sidebar-menu-link > span { flex: 1; line-height: 1.25; }
    .sidebar-menu-group { margin-top: .3rem; }
    .sidebar-menu-toggle .sidebar-menu-arrow {
        flex: 0 0 auto; margin: 0; font-size: .75rem; transition: transform .2s ease;
    }
    .sidebar-menu-group.is-open .sidebar-menu-arrow { transform: rotate(180deg); }
    .sidebar-submenu { display: none; margin-top: .25rem; padding: .15rem 0 .3rem; }
    .sidebar-menu-group.is-open .sidebar-submenu { display: block; }
    .sidebar-submenu .sidebar-menu-link { min-height: 39px; padding: .55rem .75rem .55rem 2.85rem; font-size: .88rem; }
    .sidebar-submenu .sidebar-menu-link > i:first-child { flex-basis: 8px; font-size: .35rem; }
    .main-content { margin-left: 280px; flex: 1; padding: 2rem; min-width: 0; min-height: 100vh; }
    .topbar {
        min-height: 82px; background: white; padding: 1rem 2rem; margin: -2rem -2rem 2rem;
        border-bottom: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
        display: flex; justify-content: space-between; align-items: center; gap: 1rem;
    }
    .topbar-title { font-size: 1.5rem; font-weight: 700; color: var(--navy-secondary); margin: 0; }
    .topbar-start { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .sidebar-mobile-toggle { display: none; color: var(--navy-secondary); border-color: #dbe3f1; }
    .topbar-user-toggle {
        display: flex; align-items: center; gap: .7rem; padding: .45rem .6rem;
        color: var(--navy-secondary); background: transparent; border: 1px solid transparent;
        border-radius: 9px;
    }
    .topbar-user-toggle:hover, .topbar-user-toggle:focus, .topbar-user-toggle[aria-expanded="true"] {
        color: var(--navy-secondary); background: #f8fafc; border-color: #e2e8f0;
    }
    .topbar-user-toggle::after { margin-left: .1rem; }
    .topbar-avatar {
        width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
        flex: 0 0 38px; border-radius: 50%; background: #e8edfb; color: var(--navy-secondary);
    }
    .topbar-avatar i { font-size: 1.35rem; }
    .topbar-user-copy { min-width: 0; text-align: left; line-height: 1.15; }
    .topbar-user-name { display: block; max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
    .topbar-user-role { display: block; margin-top: .2rem; color: #64748b; font-size: .72rem; text-transform: capitalize; }
    .topbar-profile-menu { min-width: 210px; padding: .45rem; border: 1px solid #e2e8f0; box-shadow: 0 12px 30px rgba(15,23,42,.12); }
    .topbar-profile-menu .dropdown-item { border-radius: 7px; padding: .65rem .75rem; }
    .topbar-profile-menu .dropdown-item:hover { background: #f1f5f9; }
    .sidebar-backdrop { display: none; }
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
    .status-assigned { color: #166534; background: #dcfce7; }
    .status-removed { color: #991b1b; background: #fee2e2; }
    .status-attendance-present { color: #166534; background: #dcfce7; }
    .status-attendance-absent { color: #991b1b; background: #fee2e2; }
    .status-attendance-late { color: #92400e; background: #fef3c7; }
    .status-draft { color: #92400e; background: #fef3c7; }
    .status-published { color: #166534; background: #dcfce7; }
    .status-paid { color: #166534; background: #dcfce7; }
    .status-partially_paid { color: #1e40af; background: #dbeafe; }
    .status-unpaid { color: #92400e; background: #fef3c7; }
    .status-overdue { color: #991b1b; background: #fee2e2; }
    .priority-normal { color: #475569; background: #e2e8f0; }
    .priority-important { color: #92400e; background: #fef3c7; }
    .priority-urgent { color: #991b1b; background: #fee2e2; }
    .role-badge { color: var(--navy-secondary); background: #e8edfb; border-radius: 999px; padding: .3rem .65rem; font-size: .78rem; font-weight: 600; }
    .empty-state { text-align: center; padding: 4rem 1rem; color: #6b7280; }
    .empty-state i { font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 1rem; }
    .detail-label { color: #6b7280; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .35rem; }
    .detail-value { color: #1f2937; font-weight: 600; margin: 0; }
    .role-card { border: 1px solid #e5e7eb; border-radius: 10px; height: 100%; padding: 1.5rem; transition: transform .2s ease, box-shadow .2s ease; }
    .role-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(30,58,138,.09); }
    .role-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #e8edfb; color: var(--navy-secondary); font-size: 1.4rem; }
    @media (max-width: 991.98px) {
        body.sidebar-open { overflow: hidden; }
        .sidebar { transform: translateX(-100%); }
        .sidebar.is-open { transform: translateX(0); }
        .sidebar-backdrop {
            position: fixed; inset: 0; z-index: 1035; background: rgba(15, 23, 42, .5);
        }
        .sidebar-backdrop.is-visible { display: block; }
        .sidebar-mobile-toggle { display: inline-flex; align-items: center; justify-content: center; }
        .main-content { margin-left: 0; padding: 1rem; }
        .topbar { min-height: 70px; margin: -1rem -1rem 1rem; padding: .75rem 1rem; }
    }
    @media (max-width: 575.98px) {
        .sidebar { width: min(86vw, 300px); }
        .topbar-title { font-size: 1.15rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .topbar-user-copy { display: none; }
        .topbar-user-toggle { gap: .35rem; padding: .35rem .45rem; }
        .content-area { padding: 1.25rem; }
        .page-heading { align-items: flex-start; flex-direction: column; }
    }
</style>
@yield('page_styles')
@endsection

@section('content')
<div class="dashboard-wrapper">
    <aside class="sidebar" id="dashboardSidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <div>
                <h4>EduPulse</h4>
                <small>School Management</small>
            </div>
        </div>

        @php
            $userMenuActive = request()->routeIs('super_admin.users.*', 'super_admin.roles.*');
            $studentMenuActive = request()->routeIs('super_admin.students.*');
            $teacherMenuActive = request()->routeIs('super_admin.teachers.*');
            $classMenuActive = request()->routeIs('super_admin.classes.*', 'super_admin.sections.*');
            $subjectMenuActive = request()->routeIs('super_admin.subjects.*');
            $assignmentMenuActive = request()->routeIs('super_admin.student_assignments.*');
            $teacherAssignmentMenuActive = request()->routeIs('super_admin.teacher_assignments.*');
            $attendanceMenuActive = request()->routeIs('attendance.*');
            $examinationMenuActive = request()->routeIs('examinations.*');
            $marksMenuActive = request()->routeIs('marks.*');
            $feesMenuActive = request()->routeIs('fees.*');
            $noticesMenuActive = request()->routeIs('notices.*');
            $reportsMenuActive = request()->routeIs('reports.*');
            $isSuperAdmin = Auth::user()->role === 'super_admin';
            $isAdmin = Auth::user()->role === 'admin';
            $isExaminationManager = $isSuperAdmin || $isAdmin;
            $dashboardRoute = match (Auth::user()->role) {
                'super_admin' => 'dashboard.super_admin',
                'admin' => 'dashboard.admin',
                'teacher' => 'dashboard.teacher',
                default => 'dashboard.student',
            };
        @endphp

        <nav aria-label="Dashboard navigation">
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route($dashboardRoute) }}" class="sidebar-menu-link {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </a>
                </li>
            </ul>

            @if ($isSuperAdmin)
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

            <div class="sidebar-menu-group {{ $classMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $classMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $classMenuActive ? 'true' : 'false' }}" aria-controls="class-section-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Class &amp; Section Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="class-section-management-menu">
                    <li>
                        <a href="{{ route('super_admin.classes.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.classes.index', 'super_admin.classes.show', 'super_admin.classes.edit') && !request('manage') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Class List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.classes.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.classes.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add Class</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.sections.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.sections.index', 'super_admin.sections.edit') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Section List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.sections.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.sections.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add Section</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.classes.index', ['manage' => 'students']) }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.classes.students') || request('manage') === 'students' ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Class Student List</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $subjectMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $subjectMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $subjectMenuActive ? 'true' : 'false' }}" aria-controls="subject-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Subject Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="subject-management-menu">
                    <li>
                        <a href="{{ route('super_admin.subjects.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.subjects.index', 'super_admin.subjects.show') && !request('manage') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Subject List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.subjects.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.subjects.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Add Subject</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.subjects.index', ['manage' => 'edit']) }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.subjects.edit') || request('manage') === 'edit' ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Edit Subject</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.subjects.classes') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.subjects.classes*') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Class Subject List</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $assignmentMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $assignmentMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $assignmentMenuActive ? 'true' : 'false' }}" aria-controls="student-class-assignment-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-person-check-fill"></i>
                    <span>Student-Class Assignment</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="student-class-assignment-menu">
                    <li>
                        <a href="{{ route('super_admin.student_assignments.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.student_assignments.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Assign Students</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.student_assignments.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.student_assignments.index', 'super_admin.student_assignments.show') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Class-wise Student List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.student_assignments.transfers') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.student_assignments.transfers') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Student Transfer</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.student_assignments.academic_years') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.student_assignments.academic_years') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Academic Year Assignment</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $teacherAssignmentMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $teacherAssignmentMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $teacherAssignmentMenuActive ? 'true' : 'false' }}" aria-controls="teacher-subject-assignment-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-person-workspace"></i>
                    <span>Teacher-Subject Assignment</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="teacher-subject-assignment-menu">
                    <li>
                        <a href="{{ route('super_admin.teacher_assignments.create') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teacher_assignments.create') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Assign Teacher</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teacher_assignments.index') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teacher_assignments.index', 'super_admin.teacher_assignments.show') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Teacher Assignment List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teacher_assignments.class_wise') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teacher_assignments.class_wise') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Class-wise Teacher List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('super_admin.teacher_assignments.subject_wise') }}" class="sidebar-menu-link {{ request()->routeIs('super_admin.teacher_assignments.subject_wise') ? 'active' : '' }}">
                            <i class="bi bi-circle-fill"></i><span>Subject-wise Teacher List</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @if ($isExaminationManager)
            <div class="sidebar-menu-group {{ $attendanceMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $attendanceMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $attendanceMenuActive ? 'true' : 'false' }}" aria-controls="attendance-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Attendance Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="attendance-management-menu">
                    <li><a href="{{ route('attendance.mark') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.mark', 'attendance.store') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Mark Attendance</span></a></li>
                    <li><a href="{{ route('attendance.index') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Attendance List</span></a></li>
                    <li><a href="{{ route('attendance.edit_list') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.edit_list', 'attendance.edit', 'attendance.update') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Edit Attendance</span></a></li>
                    <li><a href="{{ route('attendance.student_history') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.student_history') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Student Attendance History</span></a></li>
                    <li><a href="{{ route('attendance.class_report') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.class_report') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Class Attendance Report</span></a></li>
                    <li><a href="{{ route('attendance.daily_report') }}" class="sidebar-menu-link {{ request()->routeIs('attendance.daily_report') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Daily Attendance Report</span></a></li>
                </ul>
            </div>
            @endif

            <div class="sidebar-menu-group {{ $examinationMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $examinationMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $examinationMenuActive ? 'true' : 'false' }}" aria-controls="examination-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-clipboard2-data-fill"></i>
                    <span>Examination Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="examination-management-menu">
                    <li><a href="{{ route('examinations.index') }}" class="sidebar-menu-link {{ request()->routeIs('examinations.index', 'examinations.edit') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Exam List</span></a></li>
                    @if ($isExaminationManager)
                        <li><a href="{{ route('examinations.create') }}" class="sidebar-menu-link {{ request()->routeIs('examinations.create') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Add Exam</span></a></li>
                        <li><a href="{{ route('examinations.index', ['manage' => 'edit']) }}" class="sidebar-menu-link"><i class="bi bi-circle-fill"></i><span>Edit Exam</span></a></li>
                    @endif
                    <li><a href="{{ route('examinations.schedules.index') }}" class="sidebar-menu-link {{ request()->routeIs('examinations.schedules.*') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Exam Schedule</span></a></li>
                    <li><a href="{{ route('examinations.class_exams') }}" class="sidebar-menu-link {{ request()->routeIs('examinations.class_exams') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Class Exam List</span></a></li>
                    <li><a href="{{ route('examinations.upcoming') }}" class="sidebar-menu-link {{ request()->routeIs('examinations.upcoming') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Upcoming Exams</span></a></li>
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $marksMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $marksMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $marksMenuActive ? 'true' : 'false' }}" aria-controls="marks-results-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Marks / Results Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="marks-results-management-menu">
                    @if (Auth::user()->role !== 'student')
                        <li><a href="{{ route('marks.entry') }}" class="sidebar-menu-link {{ request()->routeIs('marks.entry', 'marks.store') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Marks Entry</span></a></li>
                        <li><a href="{{ route('marks.index') }}" class="sidebar-menu-link {{ request()->routeIs('marks.index') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Marks List</span></a></li>
                        <li><a href="{{ route('marks.index', ['manage' => 'edit']) }}" class="sidebar-menu-link {{ request()->routeIs('marks.edit', 'marks.update') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Edit Marks</span></a></li>
                        <li><a href="{{ route('marks.result_sheet') }}" class="sidebar-menu-link {{ request()->routeIs('marks.result_sheet') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Result Sheet</span></a></li>
                        <li><a href="{{ route('marks.class_report') }}" class="sidebar-menu-link {{ request()->routeIs('marks.class_report') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Class Result Report</span></a></li>
                        <li><a href="{{ route('marks.subject_report') }}" class="sidebar-menu-link {{ request()->routeIs('marks.subject_report') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Subject Result Report</span></a></li>
                    @else
                        <li><a href="{{ route('marks.student_results') }}" class="sidebar-menu-link {{ request()->routeIs('marks.student_results') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Student Result View</span></a></li>
                    @endif
                </ul>
            </div>

            @if ($isExaminationManager || Auth::user()->role === 'student')
            <div class="sidebar-menu-group {{ $feesMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $feesMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $feesMenuActive ? 'true' : 'false' }}" aria-controls="fees-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-cash-stack"></i>
                    <span>Fees Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="fees-management-menu">
                    @if ($isExaminationManager)
                        <li><a href="{{ route('fees.types.index') }}" class="sidebar-menu-link {{ request()->routeIs('fees.types.index', 'fees.types.edit') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Fee Types</span></a></li>
                        <li><a href="{{ route('fees.types.create') }}" class="sidebar-menu-link {{ request()->routeIs('fees.types.create') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Add Fee Type</span></a></li>
                        <li><a href="{{ route('fees.assign') }}" class="sidebar-menu-link {{ request()->routeIs('fees.assign', 'fees.assignments.store', 'fees.assignments.update') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Assign Fees</span></a></li>
                    @endif
                    <li><a href="{{ route('fees.assignments.index') }}" class="sidebar-menu-link {{ request()->routeIs('fees.assignments.index', 'fees.assignments.show') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>{{ $isExaminationManager ? 'Student Fee List' : 'My Fees' }}</span></a></li>
                    @if ($isExaminationManager)
                        <li><a href="{{ route('fees.assignments.index', ['action' => 'payment']) }}" class="sidebar-menu-link {{ request()->routeIs('fees.payments.create', 'fees.payments.store') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Record Payment</span></a></li>
                    @endif
                    <li><a href="{{ route('fees.payments.index') }}" class="sidebar-menu-link {{ request()->routeIs('fees.payments.index', 'fees.payments.edit', 'fees.payments.update') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Payment History</span></a></li>
                    @if ($isExaminationManager)
                        <li><a href="{{ route('fees.pending') }}" class="sidebar-menu-link {{ request()->routeIs('fees.pending') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Pending Fees</span></a></li>
                    @endif
                    <li><a href="{{ route('fees.payments.index') }}" class="sidebar-menu-link {{ request()->routeIs('fees.receipt') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Fee Receipt</span></a></li>
                </ul>
            </div>
            @endif

            <div class="sidebar-menu-group {{ $noticesMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $noticesMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $noticesMenuActive ? 'true' : 'false' }}" aria-controls="notice-management-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-megaphone-fill"></i>
                    <span>Notice Management</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="notice-management-menu">
                    @if ($isExaminationManager)
                        <li><a href="{{ route('notices.index') }}" class="sidebar-menu-link {{ request()->routeIs('notices.index') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Notice List</span></a></li>
                        <li><a href="{{ route('notices.create') }}" class="sidebar-menu-link {{ request()->routeIs('notices.create') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Add Notice</span></a></li>
                        <li><a href="{{ route('notices.index', ['manage' => 'edit']) }}" class="sidebar-menu-link {{ request()->routeIs('notices.edit', 'notices.update') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Edit Notice</span></a></li>
                    @endif
                    <li><a href="{{ route('notices.published') }}" class="sidebar-menu-link {{ request()->routeIs('notices.published') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Published Notices</span></a></li>
                    @if (!$isExaminationManager)
                        <li><a href="{{ route('notices.published') }}" class="sidebar-menu-link {{ request()->routeIs('notices.show') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Notice Details</span></a></li>
                    @endif
                </ul>
            </div>

            <div class="sidebar-menu-group {{ $reportsMenuActive ? 'is-open' : '' }}" data-sidebar-group>
                <button type="button" class="sidebar-menu-link sidebar-menu-toggle {{ $reportsMenuActive ? 'active' : '' }}"
                    aria-expanded="{{ $reportsMenuActive ? 'true' : 'false' }}" aria-controls="reports-menu"
                    data-sidebar-toggle>
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Reports</span>
                    <i class="bi bi-chevron-down sidebar-menu-arrow" aria-hidden="true"></i>
                </button>
                <ul class="sidebar-menu sidebar-submenu" id="reports-menu">
                    <li><a href="{{ route('reports.students') }}" class="sidebar-menu-link {{ request()->routeIs('reports.students') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Student Report</span></a></li>
                    @if ($isExaminationManager)
                        <li><a href="{{ route('reports.teachers') }}" class="sidebar-menu-link {{ request()->routeIs('reports.teachers') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Teacher Report</span></a></li>
                    @endif
                    <li><a href="{{ route('reports.attendance') }}" class="sidebar-menu-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Attendance Report</span></a></li>
                    <li><a href="{{ route('reports.exams') }}" class="sidebar-menu-link {{ request()->routeIs('reports.exams') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Exam Report</span></a></li>
                    <li><a href="{{ route('reports.marks') }}" class="sidebar-menu-link {{ request()->routeIs('reports.marks') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Marks Report</span></a></li>
                    @if ($isExaminationManager || Auth::user()->role === 'student')
                        <li><a href="{{ route('reports.fees') }}" class="sidebar-menu-link {{ request()->routeIs('reports.fees') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Fee Report</span></a></li>
                    @endif
                    @if (Auth::user()->role !== 'student')
                        <li><a href="{{ route('reports.classes') }}" class="sidebar-menu-link {{ request()->routeIs('reports.classes') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Class Report</span></a></li>
                    @endif
                    <li><a href="{{ route('reports.academic_years') }}" class="sidebar-menu-link {{ request()->routeIs('reports.academic_years') ? 'active' : '' }}"><i class="bi bi-circle-fill"></i><span>Academic Year Report</span></a></li>
                </ul>
            </div>
        </nav>

    </aside>
    <button type="button" class="sidebar-backdrop border-0" data-sidebar-close aria-label="Close sidebar"></button>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-start">
                <button type="button" class="btn sidebar-mobile-toggle" data-sidebar-open
                    aria-controls="dashboardSidebar" aria-expanded="false" aria-label="Open sidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h2 class="topbar-title">{{ $pageTitle ?? 'Dashboard' }}</h2>
            </div>
            <div class="dropdown">
                <button type="button" class="btn dropdown-toggle topbar-user-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Open user menu">
                    <span class="topbar-avatar">
                        @if(Auth::user()->profile_picture)
                            <img src="{{ asset('storage/'.Auth::user()->profile_picture) }}" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                        @else
                            <i class="bi bi-person-fill"></i>
                        @endif
                    </span>
                    <span class="topbar-user-copy">
                        <span class="topbar-user-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                        <span class="topbar-user-role">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end topbar-profile-menu">
                    <div class="px-2 py-2 d-sm-none">
                        <div class="fw-semibold text-navy">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                        <small class="text-muted text-capitalize">{{ str_replace('_', ' ', Auth::user()->role) }}</small>
                    </div>
                    <div class="dropdown-divider d-sm-none"></div>
                    <a href="{{ route('profile.index') }}" class="dropdown-item {{ request()->routeIs('profile.index') ? 'active' : '' }}">
                        <i class="bi bi-person-circle me-2"></i>My Profile
                    </a>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item {{ request()->routeIs('profile.edit', 'profile.update') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square me-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('profile.password.edit') }}" class="dropdown-item {{ request()->routeIs('profile.password.*') ? 'active' : '' }}">
                        <i class="bi bi-key me-2"></i>Change Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </div>
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
            const willOpen = !group.classList.contains('is-open');

            document.querySelectorAll('[data-sidebar-group].is-open').forEach((openGroup) => {
                if (openGroup !== group) {
                    openGroup.classList.remove('is-open');
                    openGroup.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', 'false');
                }
            });

            group.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    const sidebar = document.getElementById('dashboardSidebar');
    const sidebarBackdrop = document.querySelector('[data-sidebar-close]');
    const sidebarOpenButton = document.querySelector('[data-sidebar-open]');

    const setSidebarOpen = (isOpen) => {
        sidebar.classList.toggle('is-open', isOpen);
        sidebarBackdrop.classList.toggle('is-visible', isOpen);
        document.body.classList.toggle('sidebar-open', isOpen);
        sidebarOpenButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    sidebarOpenButton.addEventListener('click', () => setSidebarOpen(true));
    sidebarBackdrop.addEventListener('click', () => setSidebarOpen(false));
    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) setSidebarOpen(false);
        });
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) setSidebarOpen(false);
    });
</script>
@endsection
