@extends('layouts.app')
@section('title', 'Reconnaissance faciale')
@section('page-title', 'Reconnaissance faciale — Biométrie')

@section('content')

{{-- Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <i class="bi bi-people fs-1 text-primary"></i>
                <h4 class="mt-2">{{ $etudiants->total() }}</h4>
                <small class="text-muted">Étudiants inscrits</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <i class="bi bi-camera fs-1 text-success"></i>
                <h4 class="mt-2">{{ $etudiants->filter(fn($e) => $e->donneesBiometriques->isNotEmpty())->count() }}</h4>
                <small class="text-muted">Profils biométriques enregistrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <i class="bi bi-person-x fs-1 text-warning"></i>
                <h4 class="mt-2">{{ $etudiants->filter(fn($e) => $e->donneesBiometriques->isEmpty())->count() }}</h4>
                <small class="text-muted">Sans profil biométrique</small>
            </div>
        </div>
    </div>
</div>

{{-- Bouton lancer pointage (professeur uniquement) --}}
@if(auth()->user()->role === 'professeur')
@php
    $coursEnCours = \App\Models\Cours::with(['matiere','classe'])
        ->where('professeur_id', auth()->user()->professeur->id)
        ->where('statut', 'en_cours')
        ->first();
    $coursAujourdhui = \App\Models\Cours::with(['matiere','classe'])
        ->where('professeur_id', auth()->user()->professeur->id)
        ->whereDate('heureDebut', today())
        ->where('statut', 'planifie')
        ->first();
@endphp

@if($coursEnCours)
<div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="bi bi-camera-video me-2"></i>
        <strong>Cours en cours :</strong>
        {{ $coursEnCours->matiere->nomMatiere ?? '—' }} —
        {{ $coursEnCours->classe->nom ?? '—' }}
    </div>
    <a href="{{ route('biometrie.pointage', $coursEnCours) }}" class="btn btn-success">
        <i class="bi bi-camera me-2"></i>Lancer la reconnaissance faciale
    </a>
</div>
@elseif($coursAujourdhui)
<div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="bi bi-clock me-2"></i>
        <strong>Prochain cours :</strong>
        {{ $coursAujourdhui->matiere->nomMatiere ?? '—' }} —
        {{ $coursAujourdhui->classe->nom ?? '—' }}
        à {{ \Carbon\Carbon::parse($coursAujourdhui->heureDebut)->format('H:i') }}
    </div>
    <form method="POST" action="{{ route('cours.demarrer', $coursAujourdhui) }}">
        @csrf
        <button class="btn btn-primary">
            <i class="bi bi-play-fill me-2"></i>Démarrer + Lancer pointage
        </button>
    </form>
</div>
@else
<div class="alert alert-warning mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Aucun cours planifié aujourd'hui. Rendez-vous sur votre
    <a href="{{ route('professeur.dashboard') }}" class="alert-link">emploi du temps</a>
    pour démarrer un cours.
</div>
@endif
@endif

{{-- Liste étudiants --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-camera me-2"></i>Liste des étudiants</span>
        @if(auth()->user()->role === 'administrateur')
        <small class="text-muted">Cliquez sur "Enregistrer" pour ajouter le profil facial d'un étudiant</small>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Classe</th>
                        <th>Statut biométrique</th>
                        <th>Dernière mise à jour</th>
                        @if(auth()->user()->role === 'administrateur')
                        <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($etudiants as $etudiant)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#dbeafe;color:#1e40af;font-weight:700;font-size:.8rem;flex-shrink:0;">
                                {{ strtoupper(substr($etudiant->user->prenom ?? '',0,1).substr($etudiant->user->nom ?? '',0,1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $etudiant->user->prenom ?? '—' }} {{ $etudiant->user->nom ?? '' }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $etudiant->codePar }}</div>
                            </div>
                        </div>
                    </td>
                    <td><small>{{ $etudiant->inscriptionActuelle?->classe?->nom ?? '—' }}</small></td>
                    <td>
                        @if($etudiant->donneesBiometriques->isNotEmpty())
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Enregistré
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-circle me-1"></i>Non enregistré
                            </span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $etudiant->donneesBiometriques->first()?->dateEnregistre?->format('d/m/Y H:i') ?? '—' }}
                        </small>
                    </td>
                    {{-- Bouton enregistrement UNIQUEMENT pour l'admin --}}
                    @if(auth()->user()->role === 'administrateur')
                    <td>
                        <a href="{{ route('biometrie.enregistrer', $etudiant) }}"
                           class="btn btn-sm {{ $etudiant->donneesBiometriques->isNotEmpty() ? 'btn-outline-primary' : 'btn-primary' }}">
                            <i class="bi bi-camera me-1"></i>
                            {{ $etudiant->donneesBiometriques->isNotEmpty() ? 'Mettre à jour' : 'Enregistrer' }}
                        </a>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>Aucun étudiant trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $etudiants->links() }}</div>
</div>
@endsection