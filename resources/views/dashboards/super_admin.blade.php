@extends('layouts.app')

@section('title', 'Super Admin Dashboard - School Management System')

@section('styles')
<style>
    .dashboard-wrapper {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 260px;
        background: linear-gradient(135deg, var(--navy-secondary) 0%, #11224e 100%);
        color: white;
        padding: 2rem 0;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-brand {
        padding: 0 1.5rem 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 2rem;
        text-align: center;
    }

    .sidebar-brand i {
        font-size: 2.5rem;
        color: var(--amber-accent);
        display: block;
        margin-bottom: 0.5rem;
    }

    .sidebar-brand h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: white;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu-item {
        margin: 0;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        padding: 0.875rem 1.5rem;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .sidebar-menu-link:hover {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
        border-left-color: var(--amber-accent);
    }

    .sidebar-menu-link.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.15);
        border-left-color: var(--amber-accent);
        font-weight: 600;
    }

    .sidebar-menu-link i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
    }

    .sidebar-footer form {
        margin: 0;
    }

    .sidebar-footer .btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .main-content {
        margin-left: 260px;
        flex: 1;
        padding: 2rem;
        overflow-y: auto;
        min-height: 100vh;
    }

    .topbar {
        background: white;
        padding: 1rem 2rem;
        margin: -2rem -2rem 2rem -2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .topbar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--navy-secondary);
    }

    .topbar-user {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--navy-secondary);
    }

    .topbar-user i {
        font-size: 1.5rem;
    }

    .content-area {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        min-height: 60vh;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
            padding: 1rem 0;
        }

        .sidebar-footer {
            position: static;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .topbar {
            flex-direction: column;
            gap: 1rem;
            margin: -1rem -1rem 1rem -1rem;
        }

        .dashboard-wrapper {
            flex-direction: column;
        }
    }

    /* Scrollbar styling */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <h4>EduPulse</h4>
        </div>

        <nav class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('dashboard.super_admin') }}" class="sidebar-menu-link {{ Route::currentRouteName() === 'dashboard.super_admin' ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2 class="topbar-title">Dashboard</h2>
            <div class="topbar-user">
                <i class="bi bi-person-circle"></i>
                <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Empty content area for future modules -->
        </div>
    </main>
</div>
@endsection
