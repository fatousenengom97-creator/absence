<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SATIC - Gestion des Absences')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1a3a5c;
            --accent:  #2e86de;
            --sidebar-width: 260px;
        }

        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--primary);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar .brand {
            padding: 1.5rem 1.2rem;
            background: rgba(0,0,0,.2);
            color: #fff;
        }
        .sidebar .brand h5 { font-size: .95rem; font-weight: 700; margin: 0; }
        .sidebar .brand small { font-size: .7rem; opacity: .7; }

        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .65rem 1.2rem;
            border-radius: 8px;
            margin: 2px 8px;
            font-size: .875rem;
            transition: all .2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--accent);
            color: #fff;
        }
        .sidebar .nav-link i { width: 20px; margin-right: 8px; }
        .sidebar .nav-section {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 1rem 1.4rem .3rem;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 8px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; border-radius: 12px 12px 0 0 !important; font-weight: 600; }

        /* Stat cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 8px rgba(0,0,0,.06); }
        .stat-card { border-radius: 12px; padding: 1.25rem; color: #fff; }
        .stat-card .stat-icon { font-size: 2.5rem; opacity: .3; }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; }
        .stat-card .stat-label { font-size: .8rem; opacity: .85; }

        /* Badge status */
        .badge-present  { background: #d1fae5; color: #065f46; }
        .badge-absent   { background: #fee2e2; color: #991b1b; }
        .badge-retard   { background: #fef3c7; color: #92400e; }
        .badge-justifie { background: #dbeafe; color: #1e40af; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform .3s; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

@auth
<nav class="sidebar">
    <div class="brand">
        <h5><i class="bi bi-mortarboard-fill me-2"></i>UFR SATIC</h5>
        <small>Gestion des Absences</small>
    </div>

    <ul class="nav flex-column pt-2">
        {{-- Dashboard --}}
        <li class="nav-item">
            @php $role = auth()->user()->role; @endphp
            <a class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}"
               href="{{ match($role) {
                   'administrateur' => route('admin.dashboard'),
                   'etudiant'       => route('etudiant.dashboard'),
                   'professeur'     => route('professeur.dashboard'),
                   default          => route('chef.dashboard'),
               } }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>

        {{-- ADMIN --}}
        @if(auth()->user()->role === 'administrateur')
        <li class="nav-section">Administration</li>
        <li><a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i> Utilisateurs</a></li>
        <li><a class="nav-link {{ request()->routeIs('admin.classes*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}">
            <i class="bi bi-building"></i> Classes</a></li>
        <li><a class="nav-link {{ request()->routeIs('admin.matieres*') ? 'active' : '' }}" href="{{ route('admin.matieres.index') }}">
            <i class="bi bi-journal-text"></i> Matières</a></li>
        <li><a class="nav-link {{ request()->routeIs('admin.salles*') ? 'active' : '' }}" href="{{ route('admin.salles.index') }}">
            <i class="bi bi-grid"></i> Salles</a></li>

        {{-- GESTION DES COURS POUR L'ADMIN --}}
        <li><a class="nav-link {{ request()->routeIs('admin.cours*') ? 'active' : '' }}" href="{{ route('admin.cours.index') }}">
            <i class="bi bi-calendar-event"></i> Cours</a></li>
        @endif

        {{-- COURS PROFESSEUR --}}
        @if(auth()->user()->role === 'professeur')
        <li class="nav-section">Cours</li>
        <li><a class="nav-link {{ request()->routeIs('cours*') ? 'active' : '' }}" href="{{ route('cours.index') }}">
            <i class="bi bi-calendar3"></i> Cours</a></li>
        @endif

        {{-- ETUDIANT --}}
        @if(auth()->user()->role === 'etudiant')
        <li class="nav-section">Mon espace</li>
        <li><a class="nav-link {{ request()->routeIs('etudiant.cours') ? 'active' : '' }}" href="{{ route('etudiant.cours') }}">
            <i class="bi bi-calendar3"></i> Mes cours</a></li>
        <li><a class="nav-link {{ request()->routeIs('etudiant.absences') ? 'active' : '' }}" href="{{ route('etudiant.absences') }}">
            <i class="bi bi-clipboard-check"></i> Mes absences</a></li>
        @endif

        {{-- PROFESSEUR DETAILS --}}
        @if(auth()->user()->role === 'professeur')
        <li class="nav-section">Enseignement</li>
        <li><a class="nav-link {{ request()->routeIs('professeur.etudiants') ? 'active' : '' }}" href="{{ route('professeur.etudiants') }}">
            <i class="bi bi-people"></i> Mes étudiants</a></li>
        <li><a class="nav-link {{ request()->routeIs('professeur.absences') ? 'active' : '' }}" href="{{ route('professeur.absences') }}">
            <i class="bi bi-clipboard-data"></i> Absences</a></li>
        @endif

        {{-- CHEF DE SERVICE --}}
        @if(auth()->user()->role === 'chef_service')
        <li class="nav-section">Rapports</li>
        <li><a class="nav-link {{ request()->routeIs('chef.rapport') ? 'active' : '' }}" href="{{ route('chef.rapport') }}">
            <i class="bi bi-bar-chart"></i> Rapport global</a></li>
        <li><a class="nav-link {{ request()->routeIs('chef.alertes') ? 'active' : '' }}" href="{{ route('chef.alertes') }}">
            <i class="bi bi-bell"></i> Alertes</a></li>
        @endif

        {{-- ===== SECTION BIOMÉTRIE ===== --}}
        @if(auth()->user()->role === 'administrateur')
            <li class="nav-section">Biométrie</li>
            <li><a class="nav-link {{ request()->routeIs('admin.biometrie*') ? 'active' : '' }}" href="{{ route('admin.biometrie.index') }}">
                <i class="bi bi-camera"></i> Reconnaissance faciale</a></li>
        @elseif(auth()->user()->role === 'professeur')
            <li class="nav-section">Biométrie</li>
            <li><a class="nav-link {{ request()->routeIs('biometrie.index') ? 'active' : '' }}" href="{{ route('biometrie.index') }}">
                <i class="bi bi-camera"></i> Reconnaissance faciale</a></li>
        @endif

        {{-- ABSENCES GLOBALES --}}
        @if(auth()->user()->role !== 'etudiant')
            <li class="nav-section">Présences</li>
            <li>
                <a class="nav-link {{ request()->routeIs('absences.index') ? 'active' : '' }}" href="{{ route('absences.index') }}">
                    <i class="bi bi-clipboard-check"></i> Toutes les absences
                </a>
            </li>
            
            {{-- ===== SECTION STATISTIQUES ===== --}}
            @if(auth()->user()->role === 'administrateur')
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.statistiques*') ? 'active' : '' }}" href="{{ route('admin.statistiques.index') }}">
                        <i class="bi bi-pie-chart"></i> Statistiques
                    </a>
                </li>
            @elseif(auth()->user()->role !== 'chef_service')
                <li>
                    <a class="nav-link {{ request()->routeIs('absences.statistiques') ? 'active' : '' }}" href="{{ route('absences.statistiques') }}">
                        <i class="bi bi-pie-chart"></i> Statistiques
                    </a>
                </li>
            @endif
        @endif
    </ul>
</nav>

<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h6 class="mb-0 text-muted fw-normal">@yield('page-title', 'Tableau de bord')</h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill" style="background:var(--accent)">
                {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
            </span>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ auth()->user()->prenom }} {{ auth()->user()->nom }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</button>
                    </form></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="p-4">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('show');
    });
</script>
@stack('scripts')
</body>
</html>