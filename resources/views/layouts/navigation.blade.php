<nav id="mainAppNavbar" class="navbar navbar-expand-lg navbar-pro sticky-top py-2">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-danger" href="{{ route('dashboard') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 border border-danger" style="width:34px;height:34px;">
                <i class="bi bi-shield-check"></i>
            </span>
            <span>ROADSHIELD <span class="text-primary">RDC</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link nav-link-pro {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-house-door me-1"></i>Dashboard
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('admin.users.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people me-1"></i>Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('admin.vehicules.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.vehicules.index') }}">
                            <i class="bi bi-car-front me-1"></i>Véhicules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('admin.infractions.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.infractions.index') }}">
                            <i class="bi bi-clipboard-check me-1"></i>Infractions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('admin.expirations.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.expirations.index') }}">
                            <i class="bi bi-clock-history me-1"></i>Expirations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('admin.rapports.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.rapports.infractions') }}">
                            <i class="bi bi-graph-up me-1"></i>Rapports
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'agent')
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('agent.scanner*') ? 'active fw-semibold' : '' }}" href="{{ route('agent.scanner') }}">
                            <i class="bi bi-camera2 me-1"></i>Scanner OCR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('agent.infractions.*') ? 'active fw-semibold' : '' }}" href="{{ route('agent.infractions.index') }}">
                            <i class="bi bi-list-check me-1"></i>Mes PV
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'proprietaire')
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('proprietaire.vehicules') ? 'active fw-semibold' : '' }}" href="{{ route('proprietaire.vehicules') }}">
                            <i class="bi bi-car-front me-1"></i>Véhicules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-pro {{ request()->routeIs('proprietaire.infractions') ? 'active fw-semibold' : '' }}" href="{{ route('proprietaire.infractions') }}">
                            <i class="bi bi-exclamation-triangle me-1"></i>Amendes
                        </a>
                    </li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <span class="dropdown-item-text small text-muted">
                                Role: {{ ucfirst(Auth::user()->role) }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="bi bi-person-gear me-2"></i>Profil
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Deconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
