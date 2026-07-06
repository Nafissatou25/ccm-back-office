<nav class="navbar navbar-expand-lg fixed-top default-layout col-lg-12 col-12 p-0 fixed-top d-flex">
    <div class="container-fluid px-3 px-lg-1">
        {{-- Logo et toggle mobile --}}
        <div class="navbar-brand-wrapper d-flex align-items-center">
            <button class="navbar-toggler border-0 p-0 me-2 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="mdi mdi-menu" style="font-size: 1.8rem;"></span>
            </button>
            {{-- Logo --}}
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="bi bi-lightning-charge-fill text-primary" style="font-size: 1.3rem; color: #fcfcff !important;"></i>
            <span class="fw-bold" style="color: #fcfcff; font-size: 1.2rem;">CCM-SOCAD'EL</span>
        </a>
        </div>

        {{-- Contenu de la navbar (collapse) --}}
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                {{-- Profil utilisateur --}}
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center gap-2" href="#" id="UserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="fw-semibold d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        <img class="rounded-circle" src="{{ asset('images/face8.png') }}" alt="Avatar" width="36" height="36" style="object-fit: cover;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="UserDropdown" style="border-radius: 12px; min-width: 200px;">
                        <div class="dropdown-header text-center pt-3">
                            <img class="rounded-circle mb-2" src="{{ asset('images/face8.png') }}" alt="Avatar" width="48" height="48" style="object-fit: cover;">
                            <p class="mb-0 fw-bold">{{ auth()->user()->name }}</p>
                            <p class="text-muted small">{{ auth()->user()->matricule }}</p>
                        </div>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i class="mdi mdi-power"></i> Se déconnecter
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>