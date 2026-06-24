@php
    $role = strtolower(auth()->user()->role?->name ?? '');
    $isAdmin = $role === 'admin';
    $isManagerOrCS = in_array($role, ['manager', 'customer_service']);
    $isTechOrClient = in_array($role, ['technician', 'client', 'supervisor']);
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ============================================= --}}
        {{-- SECTION : ADMINISTRATION (admin uniquement)   --}}
        {{-- ============================================= --}}
        @if($isAdmin)
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="mdi mdi-view-dashboard menu-icon"></i>
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
        @endif

        {{-- ============================================= --}}
        {{-- SECTION : SUPPORT & TICKETS                   --}}
        {{-- ============================================= --}}
        @if($isManagerOrCS)
            {{-- Dashboard opérationnel (manager / CS / admin) --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="mdi mdi-chart-line menu-icon"></i>
                    <span class="menu-title">Tableau de bord</span>
                </a>
            </li>
        @endif

        {{-- Liste des tickets (visible par tous les rôles) --}}
        <li class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tickets.index') }}">
                <i class="mdi mdi-ticket menu-icon"></i>
                <span class="menu-title">Tickets</span>
            </a>
        </li>

        {{-- ============================================= --}}
        {{-- SECTION : CANAUX DE RÉCLAMATION               --}}
        {{-- ============================================= --}}
        @if($isAdmin || $isManagerOrCS)
            {{-- Demandes WhatsApp (admin / manager / CS) --}}
            <li class="nav-item {{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.whatsapp.index') }}">
                    <i class="mdi mdi-whatsapp menu-icon" style="color: #25D366;"></i>
                    <span class="menu-title">Demandes WhatsApp</span>
                    @php
                        $pendingCount = \App\Models\WhatsappRequest::where('status', 'COMPLETED')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- ============================================= --}}
        {{-- SECTION : UTILISATEUR CONNECTÉ               --}}
        {{-- ============================================= --}}
        <li class="nav-item mt-4">
            <a class="nav-link text-muted" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="mdi mdi-logout menu-icon"></i>
                <span class="menu-title">Déconnexion</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>

    </ul>
</nav>