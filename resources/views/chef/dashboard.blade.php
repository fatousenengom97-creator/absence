@extends('layouts.app')
@section('title', 'Dashboard Chef de Service')
@section('page-title', 'Tableau de bord — Chef de Service')

@section('content')
{{-- 1. CARTES DES STATISTIQUES --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-building fs-1 text-primary"></i>
                <h4 class="mt-2 fw-bold">{{ $totalClasses }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Classes</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-person-badge fs-1 text-success"></i>
                <h4 class="mt-2 fw-bold">{{ $totalProfesseurs }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Professeurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-calendar-week fs-1 text-warning"></i>
                <h4 class="mt-2 fw-bold">{{ $totalEDT }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Créneaux planifiés</small>
            </div>
        </div>
    </div>
</div>

{{-- 2. 📅 CALENDRIER HEBDOMADAIRE GLOBAL --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header border-0 d-flex justify-content-between align-items-center py-3" style="background-color: #0f172a; color: white;">
        <h6 class="m-0 fw-bold"><i class="bi bi-calendar3 me-2"></i> Planning Global des Emplois du Temps</h6>
        <span class="small fw-semibold">Lundi → Samedi • 08h00 → 19h00</span>
    </div>
    
    <div class="card-body p-0">
        @if($cours->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                <p class="mb-0">Aucun créneau n'est encore planifié dans le système.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle mb-0" style="table-layout: fixed; min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;" class="py-3 text-secondary small text-uppercase fw-bold">Heure</th>
                            <th class="fw-bold text-dark">Lundi</th>
                            <th class="fw-bold text-dark">Mardi</th>
                            <th class="fw-bold text-dark">Mercredi</th>
                            <th class="fw-bold text-dark">Jeudi</th>
                            <th class="fw-bold text-dark">Vendredi</th>
                            <th class="fw-bold text-dark">Samedi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Itération sur les tranches horaires principales (de 08h à 18h) --}}
                        @for ($h = 8; $h <= 18; $h++)
                            @php 
                                $heureCourante = sprintf('%02d:00', $h); 
                            @endphp
                            <tr>
                                {{-- Colonne Heure --}}
                                <td class="fw-bold text-secondary bg-light small">{{ $heureCourante }}</td>
                                
                                {{-- Colonnes Jours (1 = Lundi, 2 = Mardi, ..., 6 = Samedi) --}}
                                @for ($jourIndex = 1; $jourIndex <= 6; $jourIndex++)
                                    <td class="p-2" style="min-height: 90px; vertical-align: top;">
                                        @foreach($cours as $c)
                                            @php 
                                                // Analyse du jour (gère si c'est un nom textuel 'Lundi' ou une date)
                                                $jourCours = trim(strtolower($c->jour));
                                                $joursMapping = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi'];
                                                
                                                // Vérification de correspondance de l'heure de début
                                                $heureDebut = \Carbon\Carbon::parse($c->heureDebut)->format('H:i');
                                                $matchHeure = ($heureDebut === $heureCourante);
                                                
                                                // Vérification du jour
                                                $matchJour = false;
                                                if (isset($c->date)) {
                                                    $matchJour = (\Carbon\Carbon::parse($c->date)->dayOfWeekIso === $jourIndex);
                                                } else {
                                                    $matchJour = ($jourCours === $joursMapping[$jourIndex]);
                                                }
                                            @endphp

                                            @if($matchJour && $matchHeure)
                                                <div class="p-2 rounded text-start text-white shadow-sm mb-2" 
                                                     style="background-color: {{ $c->couleur ?? '#0d6efd' }}; font-size: 11px; border-left: 4px solid rgba(0,0,0,0.2);">
                                                    
                                                    {{-- Badge Classe --}}
                                                    <span class="badge bg-dark bg-opacity-50 text-uppercase mb-1" style="font-size: 9px; display: inline-block;">
                                                        {{ $c->classe->nom ?? 'N/A' }}
                                                    </span>

                                                    {{-- Matière --}}
                                                    <div class="fw-bold text-truncate" title="{{ $c->matiere->nom ?? 'N/A' }}">
                                                        {{ $c->matiere->nom ?? 'N/A' }}
                                                    </div>
                                                    
                                                    {{-- Heures --}}
                                                    <div class="text-white-50 small" style="font-size: 10px;">
                                                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }} - {{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}
                                                    </div>
                                                    
                                                    {{-- Enseignant --}}
                                                    <div class="small text-truncate" style="font-size: 10px;">
                                                        <i class="bi bi-person me-1"></i>{{ $c->professeur->name ?? 'N/A' }}
                                                    </div>
                                                    
                                                    {{-- Salle --}}
                                                    <div class="small text-truncate" style="font-size: 10px;">
                                                        <i class="bi bi-geo-alt me-1"></i>{{ $c->salle->nom ?? 'N/A' }}
                                                    </div>
                                                    
                                                    {{-- Type (CM, TD, TP) --}}
                                                    <div class="mt-1 text-end">
                                                        <span class="badge bg-light text-dark fw-bold text-uppercase" style="font-size: 8px;">{{ $c->type }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- 3. BOUTONS DE NAVIGATION DU BAS --}}
<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-week fs-1 text-primary mb-3 d-block"></i>
                <h5 class="fw-bold">Emplois du temps</h5>
                <p class="text-muted">Créer et gérer les emplois du temps par classe</p>
                <a href="{{ route('chef.edt.index') }}" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-right me-2"></i>Gérer les EDT
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-grid fs-1 text-success mb-3 d-block"></i>
                <h5 class="fw-bold">Disponibilité des salles</h5>
                <p class="text-muted">Voir les salles occupées et disponibles</p>
                <a href="{{ route('chef.salles') }}" class="btn btn-success px-4">
                    <i class="bi bi-arrow-right me-2"></i>Voir les salles
                </a>
            </div>
        </div>
    </div>
</div>
@endsection