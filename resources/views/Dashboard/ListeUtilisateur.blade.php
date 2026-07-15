@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-people me-2"></i>Tous les utilisateurs ({{ $users->total() }})</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#dbeafe;color:#1e40af;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $user->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">
                                    Créé le {{ $user->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><small>{{ $user->email }}</small></td>
                    <td>
                        @php
                            $roleColors = [
                                'administrateur' => 'bg-danger',
                                'professeur'     => 'bg-primary',
                                'etudiant'       => 'bg-success',
                                'chef_service'   => 'bg-warning text-dark',
                            ];
                        @endphp
                        <span class="badge rounded-pill {{ $roleColors[$user->role] ?? 'bg-secondary' }}">
                            {{ ucfirst(str_replace('_',' ',$user->role)) }}
                        </span>
                    </td>
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
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->role === 'etudiant' && $user->etudiant)
                            <a href="{{ route('biometrie.enregistrer', $user->etudiant) }}"
                               class="btn btn-sm btn-outline-info" title="Biométrie">
                                <i class="bi bi-camera"></i>
                            </a>
                            @endif
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Supprimer {{ $user->full_name }} ?')">
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
                    <i class="bi bi-people fs-2 d-block mb-2"></i>Aucun utilisateur trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection