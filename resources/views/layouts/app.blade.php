<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion des Absences') — UFR SATIC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @stack('styles')
    <style>
        :root {
            --sidebar-bg: #0B1F33;
            --sidebar-accent: #00D9C0;
            --sidebar-text: #cbd5e1;
            --sidebar-hover: rgba(0,217,192,.12);
            --sidebar-width: 260px;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width); height: 100vh;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            overflow-y: auto; z-index: 100;
            box-shadow: 2px 0 8px rgba(0,0,0,.2);
        }
        .sidebar-brand {
            padding: 1.4rem 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand .brand-title {
            font-size: .7rem; font-weight: 700; letter-spacing: .12em;
            color: var(--sidebar-accent); text-transform: uppercase;
        }
        .sidebar-brand .brand-sub {
            font-size: .95rem; font-weight: 600; color: #fff; margin-top: 2px;
        }
        .sidebar nav { padding: .8rem 0; flex: 1; }
        .sidebar .nav-section {
            font-size: .65rem; font-weight: 700; letter-spacing: .12em;
            color: rgba(255,255,255,.35); text-transform: uppercase;
            padding: 1rem 1.2rem .3rem;
        }
        .sidebar .nav-link {
            display: flex; align-items: center; gap: .6rem;
            padding: .55rem 1.2rem; color: var(--sidebar-text);
            border-radius: 0; font-size: .85rem; transition: all .15s;
            text-decoration: none;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--sidebar-hover);
            color: var(--sidebar-accent);
            border-left: 3px solid var(--sidebar-accent);
            padding-left: calc(1.2rem - 3px);
        }
        .sidebar .nav-link i { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 1rem 1.2rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-footer .user-name { font-size: .82rem; font-weight: 600; color: #fff; }
        .sidebar-footer .user-role { font-size: .72rem; color: var(--sidebar-accent); }

        /* ===== MAIN CONTAINER ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            background: #fff;
            padding: .9rem 1.8rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .topbar h4 { margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .page-content { padding: 1.6rem 1.8rem; flex: 1; }

        /* ===== CARDS & UI ===== */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }
        .card-header { border-radius: 12px 12px 0 0 !important; font-weight: 600; padding: .9rem 1.2rem; }
        .stat-card {
            border-radius: 14px; padding: 1.4rem; color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
        }
        .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; }
        .stat-label { font-size: .8rem; opacity: .85; margin-top: .3rem; }
        .stat-icon { font-size: 2.5rem; opacity: .25; }
        .alert { border-radius: 10px; border: none; }
        .badge { font-weight: 600; }
        .table { margin: 0; }
        .table th { font-weight: 600; font-size: .82rem; color: #64748b; border-bottom-width: 1px; }
        .table td { vertical-align: middle; font-size: .88rem; }
        .btn { border-radius: 8px; font-weight: 500; }
        .btn-sm { font-size: .78rem; }
        .form-control, .form-select { border-radius: 8px; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: #00D9C0; box-shadow: 0 0 0 3px rgba(0,217,192,.15); }
        .form-label { font-size: .85rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-title">UADB - SATIC</div>
        <div class="brand-sub">Gestion des Absences</div>
    </div>

    <nav>
        {{-- ===== ZONE GLOBALE ===== --}}
        <div class="nav-section">Général</div>
        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
            <i class="bi bi-house"></i> Accueil
        </a>

        {{-- ===== ACCÈS ADMINISTRATEUR ===== --}}
        @if(auth()->user()->role === 'administrateur')
        <div class="nav-section">Administration</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
        <a class="nav-link {{ request()->routeIs('admin.etudiants-filiere*') ? 'active' : '' }}" href="{{ route('admin.etudiants-filiere.index') }}">
            <i class="bi bi-mortarboard"></i> Étudiants par filière
        </a>
        <a class="nav-link {{ request()->routeIs('admin.departements*') ? 'active' : '' }}" href="{{ route('admin.departements.index') }}">
            <i class="bi bi-building"></i> Départements
        </a>
        <a class="nav-link {{ request()->routeIs('admin.classes*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}">
            <i class="bi bi-grid"></i> Classes
        </a>
        <a class="nav-link {{ request()->routeIs('admin.matieres*') ? 'active' : '' }}" href="{{ route('admin.matieres.index') }}">
            <i class="bi bi-journal-text"></i> Matières
        </a>
        <a class="nav-link {{ request()->routeIs('admin.salles*') ? 'active' : '' }}" href="{{ route('admin.salles.index') }}">
            <i class="bi bi-geo-alt"></i> Salles
        </a>

        <div class="nav-section">Planification</div>
        <a class="nav-link {{ request()->routeIs('cours*') ? 'active' : '' }}" href="{{ route('cours.index') }}">
            <i class="bi bi-book"></i> Gestion des cours
        </a>

        <div class="nav-section">Suivi Absences</div>
        <a class="nav-link {{ request()->routeIs('absences.index') ? 'active' : '' }}" href="{{ route('absences.index') }}">
            <i class="bi bi-clipboard-x"></i> Toutes les absences
        </a>
        <a class="nav-link {{ request()->routeIs('absences.rapport') ? 'active' : '' }}" href="{{ route('absences.rapport') }}">
            <i class="bi bi-file-pdf"></i> Rapport PDF
        </a>
        <a class="nav-link {{ request()->routeIs('absences.statistiques') ? 'active' : '' }}" href="{{ route('absences.statistiques') }}">
            <i class="bi bi-bar-chart"></i> Statistiques
        </a>

        <div class="nav-section">Biométrie</div>
        <a class="nav-link {{ request()->routeIs('biometrie*') ? 'active' : '' }}" href="{{ route('biometrie.index') }}">
            <i class="bi bi-camera"></i> Reconnaissance faciale
        </a>
        @endif

        {{-- ===== ACCÈS PROFESSEUR ===== --}}
        @if(auth()->user()->role === 'professeur')
        <div class="nav-section">Espace Enseignant</div>
        <a class="nav-link {{ request()->routeIs('professeur.dashboard') ? 'active' : '' }}" href="{{ route('professeur.dashboard') }}">
            <i class="bi bi-calendar-week"></i> Emploi du temps
        </a>
        <a class="nav-link {{ request()->routeIs('professeur.etudiants') ? 'active' : '' }}" href="{{ route('professeur.etudiants') }}">
            <i class="bi bi-people"></i> Mes étudiants
        </a>
        <a class="nav-link {{ request()->routeIs('professeur.absences') ? 'active' : '' }}" href="{{ route('professeur.absences') }}">
            <i class="bi bi-clipboard-x"></i> Absences des classes
        </a>

        <div class="nav-section">Cours & Suivi</div>
        <a class="nav-link {{ request()->routeIs('cours*') ? 'active' : '' }}" href="{{ route('cours.index') }}">
            <i class="bi bi-book"></i> Mes cours
        </a>
        <a class="nav-link {{ request()->routeIs('biometrie*') ? 'active' : '' }}" href="{{ route('biometrie.index') }}">
            <i class="bi bi-camera-video"></i> Scanner facial
        </a>
        @endif

        {{-- ===== ACCÈS ÉTUDIANT ===== --}}
        @if(auth()->user()->role === 'etudiant')
        <div class="nav-section">Espace Étudiant</div>
        <a class="nav-link {{ request()->routeIs('etudiant.dashboard') ? 'active' : '' }}" href="{{ route('etudiant.dashboard') }}">
            <i class="bi bi-calendar-week"></i> Mon emploi du temps
        </a>
        <a class="nav-link {{ request()->routeIs('etudiant.absences') ? 'active' : '' }}" href="{{ route('etudiant.absences') }}">
            <i class="bi bi-clipboard-x"></i> Mes absences
        </a>
        @endif

        {{-- ===== ACCÈS CHEF DE SERVICE ===== --}}
        @if(auth()->user()->role === 'chef_service')
        <div class="nav-section">Direction</div>
        <a class="nav-link {{ request()->routeIs('chef.dashboard') ? 'active' : '' }}" href="{{ route('chef.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard Directeur
        </a>
        <a class="nav-link {{ request()->routeIs('chef.edt*') ? 'active' : '' }}" href="{{ route('chef.edt.index') }}">
            <i class="bi bi-calendar-week"></i> Emplois du temps
        </a>
        <a class="nav-link {{ request()->routeIs('chef.salles') ? 'active' : '' }}" href="{{ route('chef.salles') }}">
            <i class="bi bi-grid"></i> Statut des salles
        </a>
        <a class="nav-link {{ request()->routeIs('chef.rapport') ? 'active' : '' }}" href="{{ route('chef.rapport') }}">
            <i class="bi bi-file-text"></i> Rapport Global
        </a>
        <a class="nav-link {{ request()->routeIs('chef.alertes') ? 'active' : '' }}" href="{{ route('chef.alertes') }}">
            <i class="bi bi-bell"></i> Alertes Seuil
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:34px;height:34px;background:rgba(0,217,192,.2);color:#00D9C0;font-weight:700;font-size:.8rem;">
                {{ strtoupper(substr(auth()->user()->prenom ?? '',0,1).substr(auth()->user()->nom ?? '',0,1)) }}
            </div>
            <div>
                <div class="user-name">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</div>
                <div class="user-role">{{ ucfirst(str_replace('_',' ', auth()->user()->role)) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,.08);color:#cbd5e1;border:1px solid rgba(255,255,255,.1);">
                <i class="bi bi-box-arrow-left me-1"></i>Déconnexion
            </button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>@yield('page-title', 'Tableau de bord')</h4>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">{{ now()->format('d/m/Y H:i') }}</span>
            <span class="badge rounded-pill" style="background:rgba(0,217,192,.15);color:#00D9C0;">
                {{ ucfirst(str_replace('_',' ', auth()->user()->role)) }}
            </span>
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>