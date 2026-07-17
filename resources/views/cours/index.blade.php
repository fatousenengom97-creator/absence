@extends('layouts.app')
@section('title', 'Mes cours')
@section('page-title', 'Mes cours')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    @if(auth()->user()->role === 'administrateur')
    <a href="{{ route('cours.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nouveau cours
    </a>
    @endif
</div>

{{-- Cours en cours aujourd'hui (avec bouton pointage bien visible) --}}
@php
    $coursEnCours = $cours->filter(fn($c) => $c->statut === 'en_cours');
@endphp

@if($coursEnCours->isNotEmpty())
<div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="bi bi-camera-video me-2 fs-5"></i>
        <strong>Cours en cours :</strong>
        {{ $coursEnCours->first()->matiere->nomMatiere ?? '—' }}
        — {{ $coursEnCours->first()->classe->nom ?? '—' }}
    </div>
    <a href="{{ route('biometrie.pointage', $coursEnCours->first()) }}"
       class="btn btn-success btn-lg">
        <i class="bi bi-camera me-2"></i>
        🎯 Lancer la reconnaissance faciale
    </a>
</div>
@endif

<div class="card">
    <div class="card-header"><i class="bi bi-book me-2"></i>Liste des cours</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Salle</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                <tr class="{{ $c->statut === 'en_cours' ? 'table-success' : '' }}">
                    <td><strong>{{ $c->matiere->nomMatiere ?? '—' }}</strong></td>
                    <td><small>{{ $c->classe->nom ?? '—' }}</small></td>
                    <td><small>{{ $c->salle->nom ?? '—' }}</small></td>
                    <td><span class="badge bg-secondary">{{ $c->typeCours }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->heureDebut)->format('d/m/Y') }}</small></td>
                    <td><span class="badge bg-primary">{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</span></td>
                    <td><span class="badge bg-secondary">{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</span></td>
                    <td>
                        @php
                            $colors = [
                                'planifie'  => 'secondary',
                                'en_cours'  => 'success',
                                'termine'   => 'dark',
                                'annule'    => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $colors[$c->statut] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $c->statut)) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($c->statut === 'planifie')
                            <form method="POST" action="{{ route('cours.demarrer', $c) }}">
                                @csrf
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-play-fill me-1"></i>Démarrer
                                </button>
                            </form>

                            @elseif($c->statut === 'en_cours')
                            {{-- BOUTON PRINCIPAL : Lancer la reconnaissance faciale --}}
                            <a href="{{ route('biometrie.pointage', $c) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-camera me-1"></i>Pointer
                            </a>
                            <form method="POST" action="{{ route('cours.terminer', $c) }}">
                                @csrf
                                <button class="btn btn-sm btn-dark">
                                    <i class="bi bi-stop-fill me-1"></i>Terminer
                                </button>
                            </form>

                            @elseif($c->statut === 'termine')
                            <a href="{{ route('absences.feuille', $c) }}"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-clipboard-check me-1"></i>Absences
                            </a>
                            @endif

                            @if(auth()->user()->role === 'administrateur')
                            <a href="{{ route('cours.edit', $c) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('cours.destroy', $c) }}"
                                  onsubmit="return confirm('Supprimer ce cours ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-book fs-2 d-block mb-2"></i>Aucun cours trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $cours->links() }}</div>
</div>
@endsection