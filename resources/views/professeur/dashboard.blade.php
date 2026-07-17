@extends('layouts.app')
@section('title', 'Mon emploi du temps')
@section('page-title', 'Mon emploi du temps')

@push('styles')
<style>
    .grille-edt { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .grille-edt th { background:#0B1F33; color:#fff; text-align:center; font-size:.8rem; padding:8px 4px; border:1px solid #1e3a5c; }
    .grille-edt td { border:1px solid #e5e7eb; padding:2px; vertical-align:top; height:55px; background:#fafafa; }
    .heure-col { width:65px!important; background:#f1f5f9!important; text-align:center; font-size:.72rem; color:#64748b; font-weight:700; vertical-align:middle!important; }
    .bloc-cours { border-radius:7px; padding:5px 7px; font-size:.7rem; color:#fff; min-height:50px; box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .bloc-cours .mat { font-weight:700; font-size:.75rem; }
    .bloc-cours .info { opacity:.9; font-size:.65rem; line-height:1.3; }
    .bloc-cours .actions { margin-top:4px; display:flex; gap:4px; }
    .btn-pointer { font-size:.6rem; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,.25); color:#fff; border:1px solid rgba(255,255,255,.4); cursor:pointer; text-decoration:none; }
    .btn-pointer:hover { background:rgba(255,255,255,.45); color:#fff; }
</style>
@endpush

@section('content')
{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center py-3">
            <i class="bi bi-book fs-2 text-primary"></i>
            <h5 class="mt-1">{{ $totalCours }}</h5>
            <small class="text-muted">Cours dispensés</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3">
            <i class="bi bi-clipboard-x fs-2 text-danger"></i>
            <h5 class="mt-1">{{ $totalAbsences }}</h5>
            <small class="text-muted">Absences enregistrées</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center py-3">
            <i class="bi bi-calendar-check fs-2 text-success"></i>
            <h5 class="mt-1">{{ $coursAujourdhui->count() }}</h5>
            <small class="text-muted">Cours aujourd'hui</small>
        </div>
    </div>
</div>

{{-- Cours du jour avec bouton pointage --}}
@if($coursAujourdhui->isNotEmpty())
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <i class="bi bi-calendar-today me-2"></i>
        Mes cours aujourd'hui — {{ now()->format('d/m/Y') }}
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Matière</th>
                    <th>Classe</th>
                    <th>Salle</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($coursAujourdhui as $c)
            <tr>
                <td><strong>{{ $c->matiere->nomMatiere ?? '—' }}</strong></td>
                <td>{{ $c->classe->nom ?? '—' }}</td>
                <td>{{ $c->salle->nom ?? '—' }}</td>
                <td><span class="badge bg-primary">{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</span></td>
                <td><span class="badge bg-secondary">{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</span></td>
                <td>
                    @php $colors=['planifie'=>'secondary','en_cours'=>'success','termine'=>'dark','annule'=>'danger']; @endphp
                    <span class="badge bg-{{ $colors[$c->statut]??'secondary' }}">
                        {{ ucfirst(str_replace('_',' ',$c->statut)) }}
                    </span>
                </td>
                <td>
                    @if($c->statut === 'planifie')
                    <form method="POST" action="{{ route('cours.demarrer', $c) }}">
                        @csrf
                        <button class="btn btn-sm btn-success">
                            <i class="bi bi-play-fill me-1"></i>Démarrer + Pointer
                        </button>
                    </form>
                    @elseif($c->statut === 'en_cours')
                    <a href="{{ route('biometrie.pointage', $c) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-camera me-1"></i>Lancer reconnaissance
                    </a>
                    @elseif($c->statut === 'termine')
                    <a href="{{ route('absences.feuille', $c) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-clipboard-check me-1"></i>Voir absences
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Navigation semaine EDT --}}
@php
    $semainePrec = $debutSemaine->copy()->subWeek()->format('Y-m-d');
    $semaineSuiv = $debutSemaine->copy()->addWeek()->format('Y-m-d');
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0B1F33;color:#fff;">
        <a href="?semaine={{ $semainePrec }}" class="btn btn-sm btn-outline-light">
            <i class="bi bi-chevron-left"></i> Précédente
        </a>
        <span>
            <i class="bi bi-calendar-week me-2"></i>
            Semaine du {{ $debutSemaine->format('d/m/Y') }} au {{ $finSemaine->format('d/m/Y') }}
        </span>
        <a href="?semaine={{ $semaineSuiv }}" class="btn btn-sm btn-outline-light">
            Suivante <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="grille-edt">
                <thead>
                    <tr>
                        <th style="width:65px;">Heure</th>
                        @foreach($jours as $i => $jour)
                        @php $date = $debutSemaine->copy()->addDays($i); @endphp
                        <th class="{{ $date->isToday() ? 'table-info' : '' }}" style="min-width:150px;">
                            {{ $jour }}<br><small style="font-weight:400;opacity:.8;">{{ $date->format('d/m') }}</small>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($heures as $h)
                @php $hStr = str_pad($h,2,'0',STR_PAD_LEFT).':00'; @endphp
                <tr>
                    <td class="heure-col">{{ $hStr }}</td>
                    @foreach($jours as $i => $jour)
                    @php
                        $date = $debutSemaine->copy()->addDays($i);

                        // Cours planifiés (table cours)
                        $coursJour = $coursSemaine->filter(fn($c) =>
                            \Carbon\Carbon::parse($c->heureDebut)->format('Y-m-d') === $date->format('Y-m-d') &&
                            \Carbon\Carbon::parse($c->heureDebut)->format('H:i') === $hStr
                        );

                        // EDT fixe (table emplois_du_temps)
                        $edtJour = isset($edtSemaine[$jour])
                            ? $edtSemaine[$jour]->filter(fn($e) => substr($e->heureDebut,0,5) === $hStr)
                            : collect();
                    @endphp
                    <td>
                        {{-- Cours planifiés --}}
                        @foreach($coursJour as $c)
                        @php $bg = '#3B82F6'; @endphp
                        <div class="bloc-cours" style="background:{{ $bg }};">
                            <div class="mat">{{ $c->matiere->nomMatiere ?? '—' }}</div>
                            <div class="info">
                                {{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}—{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}<br>
                                {{ $c->classe->nom ?? '—' }} | {{ $c->salle->nom ?? '—' }}
                            </div>
                            <div class="actions">
                                @if($c->statut === 'planifie')
                                <form method="POST" action="{{ route('cours.demarrer', $c) }}" style="margin:0;">
                                    @csrf
                                    <button class="btn-pointer"><i class="bi bi-play-fill"></i> Démarrer</button>
                                </form>
                                @elseif($c->statut === 'en_cours')
                                <a href="{{ route('biometrie.pointage', $c) }}" class="btn-pointer">
                                    <i class="bi bi-camera"></i> Pointer
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        {{-- EDT fixe --}}
                        @foreach($edtJour as $e)
                        <div class="bloc-cours" style="background:{{ $e->couleur ?? '#10B981' }};">
                            <div class="mat">{{ $e->matiere->nomMatiere ?? '—' }}</div>
                            <div class="info">
                                {{ substr($e->heureDebut,0,5) }}—{{ substr($e->heureFin,0,5) }}<br>
                                {{ $e->classe->nom ?? '—' }} | {{ $e->salle->nom ?? '—' }}
                            </div>
                            <div class="actions">
                                <span style="font-size:.6rem;background:rgba(255,255,255,.25);padding:1px 5px;border-radius:4px;">
                                    EDT fixe
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </td>
                    @endforeach
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection