@extends('layouts.app')
@section('title', 'Liste des classes')
@section('page-title', 'Gestion des classes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nouvelle classe
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-building me-2"></i>Toutes les classes ({{ $classes->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th>Filière</th>
                        <th>Niveau</th>
                        <th>Année scolaire</th>
                        <th>Effectif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($classes as $classe)
                <tr>
                    <td><strong>{{ $classe->nom }}</strong></td>
                    <td>{{ $classe->filiere->nomFiliere ?? '—' }}</td>
                    <td>
                        <span class="badge bg-info">{{ $classe->niveau->nom ?? '—' }}</span>
                    </td>
                    <td><small>{{ $classe->anneeScolaire->libelle ?? '—' }}</small></td>
                    <td>
                        <span class="badge bg-secondary">{{ $classe->effectif }} étudiant(s)</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.classes.destroy', $classe) }}"
                              onsubmit="return confirm('Supprimer cette classe ?')" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-building fs-2 d-block mb-2"></i>Aucune classe trouvée.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $classes->links() }}</div>
</div>
@endsection