@extends('layouts.app')
@section('title', 'Mon emploi du temps')
@section('page-title', 'Mon emploi du temps')

@section('content')

{{-- 1. Section Statistiques - Adaptée selon le rôle --}}
<div class="row g-4 mb-4">
    @if(auth()->user()->hasRole('professeur'))
        {{-- Vue Professeur --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <i class="bi bi-book fs-1 text-primary"></i>
                    <h4 class="mt-2">{{ $totalCours ?? 0 }}</h4>
                    <small class="text-muted">Total cours dispensés</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <i class="bi bi-clipboard-x fs-1 text-danger"></i>
                    <h4 class="mt-2">{{ $totalAbsences ?? 0 }}</h4>
                    <small class="text-muted">Absences enregistrées (Mes cours)</small>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->hasRole('student'))
        {{-- Vue Étudiant --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <i class="bi bi-mortarboard fs-1 text-primary"></i>
                    <h4 class="mt-2">{{ $classe->nom ?? 'Ma Classe' }}</h4>
                    <small class="text-muted">Classe actuelle</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <i class="bi bi-person-x fs-1 text-danger"></i>
                    <h4 class="mt-2">{{ $mesAbsencesCount ?? 0 }}</h4>
                    <small class="text-muted">Mes absences non justifiées</small>
                </div>
            </div>
        </div>
    @else
        {{-- Vue Chef de Service --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <i class="bi bi-building fs-1 text-primary"></i>
                    <h4 class="mt-2">Global</h4>
                    <small class="text-muted">Vue Supervision</small>
                </div>
            </div>
        </div>
    @endif

    {{-- Commun à tous : Nombre de cours aujourd'hui --}}
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-calendar-event fs-1 text-success"></i>
                <h4 class="mt-2">{{ isset($coursAujourdhui) ? $coursAujourdhui->count() : 0 }}</h4>
                <small class="text-muted">Cours aujourd'hui</small>
            </div>
        </div>
    </div>
</div>

{{-- Configuration des dates de navigation de la semaine --}}
@php
    $semaine = request('semaine', now()->startOfWeek()->format('Y-m-d'));
    $debutSemaine = \Carbon\Carbon::parse($semaine)->startOfWeek();
    $finSemaine   = \Carbon\Carbon::parse($semaine)->endOfWeek();
    $semainePrec  = $debutSemaine->copy()->subWeek()->format('Y-m-d');
    $semaineSuiv  = $debutSemaine->copy()->addWeek()->format('Y-m-d');
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
@endphp

{{-- 2. Emploi du temps de la semaine --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0B1F33;color:#fff;">
        <a href="?semaine={{ $semainePrec }}" class="btn btn-sm btn-outline-light">
            <i class="bi bi-chevron-left"></i> Semaine précédente
        </a>
        <span>
            <i class="bi bi-calendar-week me-2"></i>
            Semaine du {{ $debutSemaine->format('d/m/Y') }} au {{ $finSemaine->format('d/m/Y') }}
        </span>
        <a href="?semaine={{ $semaineSuiv }}" class="btn btn-sm btn-outline-light">
            Semaine suivante <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach($jours as $i => $jour)
                            @php $date = $debutSemaine->copy()->addDays($i); @endphp
                            <th class="text-center {{ $date->isToday() ? 'table-primary' : '' }}">
                                <div class="fw-bold">{{ $jour }}</div>
                                <small class="text-muted">{{ $date->format('d/m') }}</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr style="vertical-align:top;">
                        @foreach($jours as $i => $jour)
                            @php
                                $date = $debutSemaine->copy()->addDays($i);
                                $coursJour = collect();
                                
                                if(isset($coursSemaine)) {
                                    $coursJour = $coursSemaine->filter(fn($c) =>
                                        \Carbon\Carbon::parse($c->heureDebut)->format('Y-m-d') === $date->format('Y-m-d')
                                    );
                                }
                            @endphp
                            <td style="min-width:180px;padding:.5rem;">
                                @forelse($coursJour as $c)
                                    @php
                                        $colors = ['planifie'=>'#dbeafe','en_cours'=>'#d1fae5','termine'=>'#f3f4f6','annule'=>'#fee2e2'];
                                        $bg = $colors[$c->statut] ?? '#f9fafb';
                                    @endphp
                                    <div class="rounded-3 p-2 mb-2" style="background:{{ $bg }};border-left:3px solid #0B1F33;">
                                        <div class="fw-bold" style="font-size:.8rem;">{{ $c->matiere->nomMatiere ?? '—' }}</div>
                                        <div style="font-size:.72rem;color:#6b7280;">
                                            {{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}
                                            — {{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}
                                        </div>
                                        
                                        {{-- Informations conditionnelles selon le rôle --}}
                                        @if(!auth()->user()->hasRole('student'))
                                            <div style="font-size:.72rem;color:#6b7280;">
                                                <i class="bi bi-people"></i> {{ $c->classe->nom ?? '—' }}
                                            </div>
                                        @endif

                                        @if(!auth()->user()->hasRole('professeur'))
                                            <div style="font-size:.72rem;color:#6b7280;">
                                                <i class="bi bi-person"></i> {{ $c->professeur->user->name ?? '—' }}
                                            </div>
                                        @endif

                                        <div style="font-size:.72rem;color:#6b7280;">
                                            <i class="bi bi-geo-alt"></i> {{ $c->salle->nom ?? '—' }}
                                        </div>
                                        <span class="badge mb-1" style="font-size:.65rem;background:#0B1F33;color:#fff;">{{ $c->typeCours }}</span>
                                        
                                        {{-- Actions spécifiques au Professeur sur le planning --}}
                                        @if(auth()->user()->hasRole('professeur'))
                                            @if($c->statut === 'planifie')
                                                <form method="POST" action="{{ route('cours.demarrer', $c) }}" class="mt-1">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm w-100" style="font-size:.7rem;">
                                                        <i class="bi bi-play-fill"></i> Démarrer
                                                    </button>
                                                </form>
                                            @elseif($c->statut === 'en_cours')
                                                <a href="{{ route('biometrie.pointage', $c) }}" class="btn btn-primary btn-sm w-100 mt-1" style="font-size:.7rem;">
                                                    <i class="bi bi-camera"></i> Pointage
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3" style="font-size:.75rem;">Libre</div>
                                @endforelse
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 3. Tableau détaillé : Liste des cours d'aujourd'hui --}}
@if(isset($coursAujourdhui) && $coursAujourdhui->isNotEmpty())
<div class="card">
    <div class="card-header" style="background:#f8f9fa;"><i class="bi bi-calendar-check me-2"></i>Cours aujourd'hui</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        @if(!auth()->user()->hasRole('student'))
                            <th>Classe</th>
                        @endif
                        @if(!auth()->user()->hasRole('professeur'))
                            <th>Professeur</th>
                        @endif
                        <th>Salle</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        @if(auth()->user()->hasRole('professeur'))
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach($coursAujourdhui as $c)
                <tr>
                    <td><strong>{{ $c->matiere->nomMatiere ?? '—' }}</strong></td>
                    @if(!auth()->user()->hasRole('student'))
                        <td>{{ $c->classe->nom ?? '—' }}</td>
                    @endif
                    @if(!auth()->user()->hasRole('professeur'))
                        <td>{{ $c->professeur->user->name ?? '—' }}</td>
                    @endif
                    <td>{{ $c->salle->nom ?? '—' }}</td>
                    <td><span class="badge bg-primary">{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</span></td>
                    <td><span class="badge bg-secondary">{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</span></td>
                    <td>
                        @php $colors = ['planifie'=>'secondary','en_cours'=>'success','termine'=>'dark','annule'=>'danger']; @endphp
                        <span class="badge bg-{{ $colors[$c->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$c->statut)) }}</span>
                    </td>
                    @if(auth()->user()->hasRole('professeur'))
                    <td>
                        @if($c->statut === 'planifie')
                        <form method="POST" action="{{ route('cours.demarrer', $c) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success"><i class="bi bi-play-fill"></i> Démarrer</button>
                        </form>
                        @elseif($c->statut === 'en_cours')
                        <a href="{{ route('biometrie.pointage', $c) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-camera"></i> Pointage
                        </a>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection