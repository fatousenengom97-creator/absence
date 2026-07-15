@extends('layouts.app')
@section('title', 'Mes étudiants')
@section('page-title', 'Mes étudiants')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-people me-2"></i>Étudiants de mes classes ({{ $etudiants->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Code</th>
                        <th>Classe</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($etudiants as $etudiant)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:34px;height:34px;background:#d1fae5;color:#065f46;font-weight:700;font-size:.8rem;flex-shrink:0;">
                                {{ strtoupper(substr($etudiant->user->prenom ?? '',0,1).substr($etudiant->user->nom ?? '',0,1)) }}
                            </div>
                            <div class="fw-semibold small">{{ $etudiant->user->prenom ?? '—' }} {{ $etudiant->user->nom ?? '' }}</div>
                        </div>
                    </td>
                    <td><code class="small">{{ $etudiant->codePar }}</code></td>
                    <td><small>{{ $etudiant->inscriptionActuelle?->classe?->nom ?? '—' }}</small></td>
                    <td><small>{{ $etudiant->user->email ?? '—' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-5">
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