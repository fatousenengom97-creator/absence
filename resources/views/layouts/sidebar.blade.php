<div class="sidebar">
    <div class="brand">
        <h5>UADB - SATIC</h5>
        <small>Gestion des Absences & EDT</small>
    </div>

    <div class="py-3">
        <!-- =================== ACCUELS COMMUNS =================== -->
        <div class="nav-section">Général</div>
        <a href="/" class="nav-link {{ Request::is('/') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i> Accueil
        </a>

        <!-- =================== MENU ADMINISTRATEUR =================== -->
        @if(auth()->user()->role === 'administrateur')
            <div class="nav-section">Administration</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard Admin
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Gestion Utilisateurs
            </a>
            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ Request::routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark"></i> Gestion Classes
            </a>
            <a href="{{ route('admin.departements.index') }}" class="nav-link {{ Request::routeIs('admin.departements.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Départements & UE
            </a>
        @endif

        <!-- =================== MENU CHEF DE SERVICE =================== -->
        @if(auth()->user()->role === 'chef_service')
            <div class="nav-section">Chef de Service</div>
            <a href="{{ route('chef.dashboard') }}" class="nav-link {{ Request::routeIs('chef.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard Chef
            </a>
            <a href="{{ route('chef.edt.index') }}" class="nav-link {{ Request::routeIs('chef.edt.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Emplois du Temps
            </a>
            <a href="{{ route('chef.rapport') }}" class="nav-link {{ Request::routeIs('chef.rapport') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Rapport Global
            </a>
            <a href="{{ route('chef.alertes') }}" class="nav-link {{ Request::routeIs('chef.alertes') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i> Alertes Absences
            </a>
        @endif

        <!-- =================== MENU PROFESSEUR =================== -->
        @if(auth()->user()->role === 'professeur')
            <div class="nav-section">Espace Enseignant</div>
            <a href="{{ route('professeur.dashboard') }}" class="nav-link {{ Request::routeIs('professeur.dashboard') ? 'active' : '' }}">
                <i class="bi bi-columns-gap"></i> Mon Espace
            </a>
            <a href="{{ route('professeur.etudiants') }}" class="nav-link {{ Request::routeIs('professeur.etudiants') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Mes Étudiants
            </a>
            <a href="{{ route('professeur.absences') }}" class="nav-link {{ Request::routeIs('professeur.absences') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Saisir Absences
            </a>
        @endif

        <!-- =================== MENU ETUDIANT =================== -->
        @if(auth()->user()->role === 'etudiant')
            <div class="nav-section">Espace Étudiant</div>
            <a href="{{ route('etudiant.dashboard') }}" class="nav-link {{ Request::routeIs('etudiant.dashboard') ? 'active' : '' }}">
                <i class="bi bi-mortarboard"></i> Mon Bureau
            </a>
            <a href="{{ route('etudiant.absences') }}" class="nav-link {{ Request::routeIs('etudiant.absences') ? 'active' : '' }}">
                <i class="bi bi-calendar-x"></i> Mes Absences
            </a>
            
            <!-- Lien dynamique vers son propre emploi du temps s'il a une classe -->
            @if(auth()->user()->etudiant && auth()->user()->etudiant->classe)
                {{-- Correction ici : Remplacement de ->id par ->idClasse pour correspondre aux modèles et au contrôleur --}}
                <a href="{{ route('chef.edt.classe', auth()->user()->etudiant->classe->idClasse) }}" class="nav-link {{ Request::is('chef/emploi-du-temps/' . auth()->user()->etudiant->classe->idClasse) ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Mon Emploi du Temps
                </a>
            @endif
        @endif

        <!-- =================== OUTILS BIOMÉTRIE & LOGOUT =================== -->
        <div class="nav-section">Système</div>
        <a href="{{ route('biometrie.index') }}" class="nav-link {{ Request::routeIs('biometrie.*') ? 'active' : '' }}">
            <i class="bi bi-fingerprint"></i> Module Biométrique
        </a>
        
        <div class="px-3 mt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger w-100 align-items-center">
                    <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                </button>
            </form>
        </div>
    </div>
</div>