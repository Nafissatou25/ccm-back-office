@php
    $role = strtolower(auth()->user()->role?->name ?? '');
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ================= ADMIN ================= --}}
        @if($role === 'admin')

            {{-- DASHBOARD ADMIN --}}
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            {{-- USERS --}}
            <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Utilisateurs</span>
                </a>
            </li>

            {{-- UNITES --}}
            <li class="nav-item {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.units.index') }}">
                    <i class="menu-icon mdi mdi-domain"></i>
                    <span class="menu-title">Unités</span>
                </a>
            </li>

            {{-- AGENCES --}}
            <li class="nav-item {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.agencies.index') }}">
                    <i class="menu-icon mdi mdi-bank"></i>
                    <span class="menu-title">Agences</span>
                </a>
            </li>

            {{-- TYPES --}}
            <li class="nav-item {{ request()->routeIs('admin.types.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.types.index') }}">
                    <i class="menu-icon mdi mdi-tag-multiple"></i>
                    <span class="menu-title">Types réclamations</span>
                </a>
            </li>

            {{-- SLA --}}
            <li class="nav-item {{ request()->routeIs('admin.slaRules.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.slaRules.index') }}">
                    <i class="menu-icon mdi mdi-format-list-bulleted-type"></i>
                    <span class="menu-title">Règles SLA</span>
                </a>
            </li>

        {{-- ================= USERS (manager, tech, client) ================= --}}
        @else

            {{-- DASHBOARD USER --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- TICKETS --}}
            <li class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tickets.index') }}">
                    <i class="menu-icon mdi mdi-ticket"></i>
                    <span class="menu-title">Tickets</span>
                </a>
            </li>

        @endif

    </ul>
</nav>