@extends('layouts.app')
@section('title', 'Professeurs')
@section('page-title', 'Liste des professeurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-badge me-2 text-primary"></i>Tous les professeurs ({{ $professeurs->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Professeur</th>
                        <th>Matricule</th>
                        <th>Email</th>
                        <th>Spécialité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($professeurs as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#dbeafe;color:#1e40af;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                            </div>
                            <div class="fw-semibold small">{{ $user->prenom }} {{ $user->nom }}</div>
                        </div>
                    </td>
                    <td><code class="small">{{ $user->professeur->matricule ?? '—' }}</code></td>
                    <td><small>{{ $user->email }}</small></td>
                    <td><small>{{ $user->professeur->specialite ?? '—' }}</small></td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success rounded-pill">Actif</span>
                        @else
                            <span class="badge bg-secondary rounded-pill">Inactif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Supprimer {{ $user->prenom }} {{ $user->nom }} ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-person-badge fs-2 d-block mb-2"></i>Aucun professeur trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $professeurs->links() }}</div>
</div>
@endsection