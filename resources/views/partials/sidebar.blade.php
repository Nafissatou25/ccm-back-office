@php
    $role = strtolower(auth()->user()->role?->name ?? '');
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ========== ADMIN UNIQUEMENT ========== --}}
        @if($role === 'admin')

            {{-- Admin Dashboard --}}
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="mdi mdi-view-dashboard menu-icon"></i>
                    <span class="menu-title">Dashboard Admin</span>
                </a>
            </li>

            {{-- Dashboard Tickets (accessible aussi à l'admin) --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="mdi mdi-chart-line menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="mdi mdi-account-group menu-icon"></i>
                    <span class="menu-title">Utilisateurs</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.units.index') }}">
                    <i class="mdi mdi-domain menu-icon"></i>
                    <span class="menu-title">Unités</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.agencies.index') }}">
                    <i class="mdi mdi-bank menu-icon"></i>
                    <span class="menu-title">Agences</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.types.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.types.index') }}">
                    <i class="mdi mdi-tag-multiple menu-icon"></i>
                    <span class="menu-title">Types réclamations</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.slaRules.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.slaRules.index') }}">
                    <i class="mdi mdi-format-list-bulleted-type menu-icon"></i>
                    <span class="menu-title">Règles SLA</span>
                </a>
            </li>

        @elseif($role === 'manager'|| 'customer_service')
            {{-- ========== MANAGER ========== --}}
            {{-- Dashboard Tickets --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="mdi mdi-chart-line menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            {{-- Tickets --}}
            <li class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tickets.index') }}">
                    <i class="mdi mdi-ticket menu-icon"></i>
                    <span class="menu-title">Tickets</span>
                </a>
            </li>

        @else
            {{-- ========== TECHNICIEN, CLIENT, SUPERVISOR, etc. ========== --}}
            {{-- Aucun dashboard (seulement Tickets) --}}
            <li class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tickets.index') }}">
                    <i class="mdi mdi-ticket menu-icon"></i>
                    <span class="menu-title">Tickets</span>
                </a>
            </li>

        @endif

    </ul>
</nav>