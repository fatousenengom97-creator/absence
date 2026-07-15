@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="my-4">
        <h3 class="fw-bold text-danger"><i class="bi bi-bell-fill me-2"></i>Suivi des Alertes Pédagogiques</h3>
        <p class="text-muted">Liste des étudiants ayant cumulé plus de 3 absences (Seuil d'avertissement).</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Étudiant</th>
                            <th>Email</th>
                            <th>Filière & Niveau</th>
                            <th class="text-center">Total Absences</th>
                            <th class="text-end pe-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($etudiants as $etudiant)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $etudiant->user->name ?? 'N/A' }}</td>
                                <td>{{ $etudiant->user->email ?? 'N/A' }}</td>
                                <td>{{ $etudiant->classe->filiere->nom ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $etudiant->nb_absences > 5 ? 'danger' : 'warning' }} px-3 py-2 fs-6">
                                        {{ $etudiant->nb_absences }} absences
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($etudiant->nb_absences > 5)
                                        <span class="badge bg-light text-danger border border-danger">Exclusion temporaire</span>
                                    @else
                                        <span class="badge bg-light text-warning border border-warning">Avertissement</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Aucun étudiant en alerte pour le moment. Félicitations à l'UFR !</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($etudiants->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $etudiants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection