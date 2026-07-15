@extends('layouts.app')
@section('title', 'Cours')
@section('page-title', 'Gestion des cours')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    @if(auth()->user()->isAdmin() || auth()->user()->isProfesseur())
    <a href="{{ route('cours.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nouveau cours
    </a>
    @endif
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Liste des cours ({{ $cours->total() }})</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Professeur</th>
                        <th>Classe</th>
                        <th>Salle</th>
                        <th>Date & Heure</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                <tr>
                    <td>
                        <div class="fw-semibold small">{{ $c->matiere->nomMatiere }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $c->matiere->codeUE }}</div>
                    </td>
                    <td><small>{{ $c->professeur->user->full_name }}</small></td>
                    <td><small>{{ $c->classe->nom }}</small></td>
                    <td><small>{{ $c->salle->nom }}</small></td>
                    <td>
                        <div class="small">{{ $c->heureDebut->format('d/m/Y') }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $c->heureDebut->format('H:i') }} → {{ $c->heureFin->format('H:i') }}
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill
                            {{ $c->statut === 'en_cours'  ? 'bg-success' :
                              ($c->statut === 'termine'   ? 'bg-secondary' :
                              ($c->statut === 'annule'    ? 'bg-danger' : 'bg-warning text-dark')) }}">
                            {{ ucfirst(str_replace('_',' ',$c->statut)) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('cours.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('absences.feuille', $c) }}" class="btn btn-sm btn-outline-success"
                               title="Feuille de présence">
                                <i class="bi bi-list-check"></i>
                            </a>
                            @if(auth()->user()->isProfesseur() && $c->statut === 'planifie')
                            <form method="POST" action="{{ route('professeur.pointage', $c) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-info" title="Démarrer pointage facial">
                                    <i class="bi bi-camera-video"></i>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('cours.edit', $c) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('cours.destroy', $c) }}"
                                  onsubmit="return confirm('Supprimer ce cours ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun cours trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $cours->links() }}</div>
</div>
@endsection