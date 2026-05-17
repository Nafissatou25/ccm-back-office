@php
    $role = strtolower(auth()->user()->role?->name ?? '');
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ================= ADMIN ================= --}}
        @if($role === 'admin')

            {{-- DASHBOARD --}}
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
    </a>
</li>

            {{-- USERS --}}
            <li class="nav-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Utilisateurs</span>
                </a>
            </li>

            {{-- ROLES --}}
            <li class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-shield-account"></i>
                    <span class="menu-title">Rôles</span>
                </a>
            </li>

            {{-- UNITES --}}
            <li class="nav-item {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-office-building"></i>
                    <span class="menu-title">Unités</span>
                </a>
            </li>

            {{-- TYPES --}}
            <li class="nav-item {{ request()->routeIs('admin.types.*') ? 'active' : '' }}">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-format-list-bulleted-type"></i>
                    <span class="menu-title">Types réclamations</span>
                </a>
            </li>

            {{-- SLA --}}
            <li class="nav-item {{ request()->routeIs('admin.sla.*') ? 'active' : '' }}">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-timer-cog-outline"></i>
                    <span class="menu-title">SLA</span>
                </a>
            </li>

        @else

            {{-- ================= UTILISATEURS NORMAUX ================= --}}

            <li class="nav-item {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tickets.index') }}">
                    <i class="menu-icon mdi mdi-ticket"></i>
                    <span class="menu-title">Tickets</span>
                </a>
            </li>

        @endif

    </ul>
</nav>