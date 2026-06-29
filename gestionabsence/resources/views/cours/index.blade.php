@extends('layouts.app')
@section('title', 'Cours')
@section('page-title', 'Gestion des cours')

@php
    $role = auth()->user()->role;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    @if($role === 'administrateur' || $role === 'chef_service')
        <a href="{{ route('cours.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Planifier un cours
        </a>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar3 me-2"></i>Liste des cours ({{ $cours->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Type</th>
                        @if($role !== 'professeur')
                            <th>Professeur</th>
                        @endif
                        @if($role !== 'etudiant')
                            <th>Classe</th>
                        @endif
                        <th>Salle</th>
                        <th>Jour</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        @if($role === 'administrateur' || $role === 'chef_service')
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                    <tr>
                        <td class="fw-semibold small">{{ $c->matiere->nomMatiere ?? '—' }}</td>
                        <td>
                            @php $type = $c->typeCours ?? null; @endphp
                            @if($type === 'CM')
                                <span class="badge bg-primary">CM</span>
                            @elseif($type === 'TD')
                                <span class="badge bg-info text-dark">TD</span>
                            @elseif($type === 'TP')
                                <span class="badge bg-warning text-dark">TP</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if($role !== 'professeur')
                            <td><small>{{ $c->professeur->user->prenom ?? '' }} {{ $c->professeur->user->nom ?? '—' }}</small></td>
                        @endif
                        @if($role !== 'etudiant')
                            <td><small>{{ $c->classe->nom ?? '—' }}</small></td>
                        @endif
                        <td><small>{{ $c->salle->nom ?? '—' }}</small></td>
                        <td><small>{{ $c->jour ?? '—' }}</small></td>
                        <td><small>{{ \Illuminate\Support\Carbon::parse($c->heureDebut)->format('d/m/Y H:i') }}</small></td>
                        <td><small>{{ $c->heureFin ? \Illuminate\Support\Carbon::parse($c->heureFin)->format('d/m/Y H:i') : '—' }}</small></td>
                        <td>
                            @php $statut = $c->statut ?? null; @endphp
                            @if($statut === 'planifie')
                                <span class="badge bg-secondary rounded-pill">Planifié</span>
                            @elseif($statut === 'en_cours')
                                <span class="badge bg-success rounded-pill">En cours</span>
                            @elseif($statut === 'termine')
                                <span class="badge bg-dark rounded-pill">Terminé</span>
                            @elseif($statut === 'annule')
                                <span class="badge bg-danger rounded-pill">Annulé</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if($role === 'administrateur' || $role === 'chef_service')
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('cours.show', $c) }}" class="btn btn-sm btn-outline-secondary" title="Détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('cours.edit', $c) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cours.destroy', $c) }}"
                                          onsubmit="return confirm('Supprimer ce cours ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun cours trouvé.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $cours->links() }}</div>
</div>
@endsection