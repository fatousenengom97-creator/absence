@extends('layouts.app')
@section('title', 'Alertes absences')
@section('page-title', 'Alertes — Étudiants ayant 5 absences ou plus')

@section('content')
<div class="card">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-bell me-2"></i>Étudiants ayant 5 absences ou plus
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Classe</th>
                        <th>Nombre d'absences</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($etudiantsEnAlerte as $etudiant)
                <tr>
                    <td>
                        <div class="fw-semibold small">
                            {{ $etudiant->user->prenom ?? '—' }}
                            {{ $etudiant->user->nom ?? '' }}
                        </div>
                    </td>
                    <td><small>{{ $etudiant->inscriptionActuelle?->classe?->nom ?? '—' }}</small></td>
                    <td>
                        <span class="badge bg-danger">{{ $etudiant->absences_count }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                    Aucun étudiant en alerte.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection