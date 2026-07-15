@extends('layouts.app')
@section('title', $classe->nom)
@section('page-title', 'Étudiants — ' . $classe->nom)

@section('content')
<nav class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.etudiants-filiere.index') }}">Filières</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.etudiants-filiere.classes', $filiere) }}">{{ $filiere->nomFiliere }}</a></li>
        <li class="breadcrumb-item active">{{ $classe->nom }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">{{ $classe->nom }} <span class="badge bg-info ms-2">{{ $classe->niveau->nom ?? '—' }}</span></h5>
        <small class="text-muted">{{ $etudiants->total() }} étudiant(s) inscrit(s)</small>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un étudiant
    </a>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-mortarboard me-2 text-success"></i>Liste des étudiants</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Code</th>
                        <th>Email</th>
                        <th>Date de naissance</th>
                        <th>Lieu de naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($etudiants as $etudiant)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#d1fae5;color:#065f46;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                {{ strtoupper(substr($etudiant->user->prenom,0,1).substr($etudiant->user->nom,0,1)) }}
                            </div>
                            <div class="fw-semibold small">{{ $etudiant->user->prenom }} {{ $etudiant->user->nom }}</div>
                        </div>
                    </td>
                    <td><code class="small">{{ $etudiant->codePar }}</code></td>
                    <td><small>{{ $etudiant->user->email }}</small></td>
                    <td><small>{{ $etudiant->dateNaissance?->format('d/m/Y') ?? '—' }}</small></td>
                    <td><small class="text-muted">{{ $etudiant->lieuNaissance ?? '—' }}</small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.users.edit', $etudiant->user) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('biometrie.enregistrer', $etudiant) }}" class="btn btn-sm btn-outline-info" title="Biométrie">
                                <i class="bi bi-camera"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>Aucun étudiant dans cette classe.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $etudiants->links() }}</div>
</div>
@endsection