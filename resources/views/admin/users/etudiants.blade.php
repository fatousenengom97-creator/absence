@extends('layouts.app')
@section('title', 'Étudiants')
@section('page-title', 'Liste des étudiants')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="card mb-3">
    <div class="card-header bg-light">
        <i class="bi bi-mortarboard me-2 text-success"></i>Tous les étudiants ({{ $totalEtudiants }})
    </div>
</div>

@forelse($etudiantsParFiliere as $nomFiliere => $classesGroupees)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:#065f46;color:#fff;">
            <span><i class="bi bi-diagram-3 me-2"></i>{{ $nomFiliere }}</span>
            <span class="badge bg-light text-dark">
                {{ $classesGroupees->flatten()->count() }} étudiant(s)
            </span>
        </div>

        <div class="card-body p-0">
            @foreach($classesGroupees as $nomClasse => $usersDeLaClasse)
                <div class="border-bottom">
                    <div class="px-3 py-2 bg-body-secondary fw-semibold small d-flex justify-content-between">
                        <span><i class="bi bi-people me-2"></i>{{ $nomClasse }}</span>
                        <span class="text-muted">{{ $usersDeLaClasse->count() }} étudiant(s)</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Code</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($usersDeLaClasse as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width:36px;height:36px;background:#d1fae5;color:#065f46;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                                {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                                            </div>
                                            <div class="fw-semibold small">{{ $user->prenom }} {{ $user->nom }}</div>
                                        </div>
                                    </td>
                                    <td><code class="small">{{ $user->etudiant->codePar ?? '—' }}</code></td>
                                    <td><small>{{ $user->email }}</small></td>
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
                                            @if($user->etudiant)
                                            <a href="{{ route('biometrie.enregistrer', $user->etudiant) }}" class="btn btn-sm btn-outline-info" title="Biométrie">
                                                <i class="bi bi-camera"></i>
                                            </a>
                                            @endif
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
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>Aucun étudiant trouvé.
        </div>
    </div>
@endforelse
@endsection