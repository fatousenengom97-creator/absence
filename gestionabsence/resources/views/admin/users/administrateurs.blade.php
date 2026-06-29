@extends('layouts.app')
@section('title', 'Administrateurs')
@section('page-title', 'Liste des administrateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-shield-lock me-2 text-danger"></i>Tous les administrateurs ({{ $administrateurs->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Administrateur</th>
                        <th>Email</th>
                        <th>Niveau d'accès</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($administrateurs as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#fee2e2;color:#991b1b;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                            </div>
                            <div class="fw-semibold small">{{ $user->prenom }} {{ $user->nom }}</div>
                        </div>
                    </td>
                    <td><small>{{ $user->email }}</small></td>
                    <td><span class="badge bg-dark">{{ $user->administrateur->niveauAcces ?? '—' }}</span></td>
                    <td><small>{{ $user->telephone ?? '—' }}</small></td>
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
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Supprimer {{ $user->prenom }} {{ $user->nom }} ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>Aucun administrateur trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $administrateurs->links() }}</div>
</div>
@endsection