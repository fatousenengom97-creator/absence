@extends('layouts.app')
@section('title', 'Mon espace')
@section('page-title', 'Mon espace étudiant')

@push('styles')
<style>
    .grille-edt { border-collapse:collapse; width:100%; table-layout:fixed; }
    .grille-edt th { background:#0B1F33; color:#fff; text-align:center; font-size:.78rem; padding:8px 4px; border:1px solid #1e3a5c; }
    .grille-edt td { border:1px solid #e5e7eb; padding:2px; vertical-align:top; height:50px; background:#fafafa; }
    .heure-col { width:60px!important; background:#f1f5f9!important; text-align:center; font-size:.7rem; color:#64748b; font-weight:700; vertical-align:middle!important; }
    .bloc-cours { border-radius:7px; padding:5px 7px; font-size:.68rem; color:#fff; min-height:46px; box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .bloc-cours .mat { font-weight:700; font-size:.72rem; }
    .bloc-cours .info { opacity:.9; font-size:.62rem; line-height:1.3; }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center py-3">
            <i class="bi bi-mortarboard fs-2 text-primary"></i>
            <h6 class="mt-1 mb-0 small">{{ $classe?->nom ?? '—' }}</h6>
            <small class="text-muted">Ma classe</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <i class="bi bi-book fs-2 text-success"></i>
            <h5 class="mt-1">{{ $coursAujourdhui->count() }}</h5>
            <small class="text-muted">Cours aujourd'hui</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <i class="bi bi-clipboard-x fs-2 text-danger"></i>
            <h5 class="mt-1">{{ $totalAbsences }}</h5>
            <small class="text-muted">Absences</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <i class="bi bi-patch-check fs-2 text-info"></i>
            <h5 class="mt-1">{{ $totalJustifies }}</h5>
            <small class="text-muted">Justifiées</small>
        </div>
    </div>
</div>

{{-- Cours aujourd'hui --}}
@if($coursAujourdhui->isNotEmpty())
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <i class="bi bi-calendar-today me-2"></i>Mes cours aujourd'hui — {{ now()->format('d/m/Y') }}
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Matière</th>
                    <th>Professeur</th>
                    <th>Salle</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Type</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            @foreach($coursAujourdhui as $c)
            <tr>
                <td><strong>{{ $c->matiere?->nomMatiere ?? '—' }}</strong></td>
                <td><small>{{ $c->professeur?->user?->prenom ?? '—' }} {{ $c->professeur?->user?->nom ?? '' }}</small></td>
                <td><small>{{ $c->salle?->nom ?? '—' }}</small></td>
                <td><span class="badge bg-primary">{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</span></td>
                <td><span class="badge bg-secondary">{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</span></td>
                <td><span class="badge bg-dark">{{ $c->typeCours }}</span></td>
                <td>
                    @php $colors=['planifie'=>'secondary','en_cours'=>'success','termine'=>'dark','annule'=>'danger']; @endphp
                    <span class="badge bg-{{ $colors[$c->statut]??'secondary' }}">
                        {{ ucfirst(str_replace('_',' ',$c->statut)) }}
                    </span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- EDT de la semaine --}}
@if($classe)
@php
    $jours  = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $heures = range(8, 18);
@endphp
<div class="card mb-4">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-calendar-week me-2"></i>
        Mon emploi du temps — {{ $classe->nom }}
        <small style="color:#00D9C0;float:right;">Lundi → Samedi • 08h → 19h</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="grille-edt">
                <thead>
                    <tr>
                        <th style="width:60px;">Heure</th>
                        @foreach($jours as $jour)
                        <th style="min-width:130px;">{{ $jour }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($heures as $h)
                @php $hStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                <tr>
                    <td class="heure-col">{{ $hStr }}</td>
                    @foreach($jours as $jour)
                    @php
                        $creneaux = $edtClasse->filter(function($e) use ($jour, $hStr) {
                            return $e->jour === $jour && substr($e->heureDebut, 0, 5) === $hStr;
                        });
                    @endphp
                    <td>
                        @foreach($creneaux as $e)
                        <div class="bloc-cours" style="background:{{ $e->couleur ?? '#3B82F6' }};">
                            <div class="mat">{{ $e->matiere?->nomMatiere ?? '—' }}</div>
                            <div class="info">
                                {{ substr($e->heureDebut,0,5) }}—{{ substr($e->heureFin,0,5) }}<br>
                                👤 {{ $e->professeur?->user?->prenom ?? '' }} {{ $e->professeur?->user?->nom ?? '' }}<br>
                                📍 {{ $e->salle?->nom ?? '—' }}
                                <span style="background:rgba(255,255,255,.25);padding:0 4px;border-radius:3px;font-size:.6rem;">{{ $e->typeCours }}</span>
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
@endif

{{-- Dernières absences --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard-x me-2"></i>Mes dernières absences</span>
        <a href="{{ route('etudiant.absences') }}" class="btn btn-sm btn-outline-primary">
            Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Matière</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            @forelse($dernieresAbsences as $absence)
            <tr>
                <td><small>{{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</small></td>
                <td><strong>{{ $absence->cours?->matiere?->nomMatiere ?? '—' }}</strong></td>
                <td>
                    @php $colors=['present'=>'success','absent'=>'danger','retard'=>'warning','justifie'=>'info']; @endphp
                    <span class="badge bg-{{ $colors[$absence->statut]??'secondary' }}">
                        {{ ucfirst($absence->statut) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-3">
                    <i class="bi bi-check-circle text-success me-1"></i>Aucune absence enregistrée.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection