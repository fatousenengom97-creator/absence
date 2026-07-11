@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h2 class="fw-bold">Statistiques des Absences</h2>
        <p class="text-muted">Analyse globale des présences et des absences par classe et par étudiant.</p>
    </div>

    <!-- Grille de cartes de statistiques dynamiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 uppercase fw-semibold">Taux de Présence Global</h6>
                    <h2 class="fw-bold mb-0 mt-2">{{ $tauxPresenceGlobal }} %</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 uppercase fw-semibold">Total Absences</h6>
                    <h2 class="fw-bold mb-0 mt-2">{{ $totalAbsences }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white">
                <div class="card-body p-4">
                    <h6 class="text-white-50 uppercase fw-semibold">Étudiants Réguliers</h6>
                    <h2 class="fw-bold mb-0 mt-2">{{ $etudiantsReguliersCount }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Évolution/Détails par classe -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Situation par Classe</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nom de la Classe</th>
                            <th>Nombre d'Étudiants</th>
                            <th>Total Absences cumulées</th>
                            <th>Taux de Présence</th>
                            <th>Taux d'Absence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $classe)
                            <tr>
                                <td class="fw-bold">{{ $classe->nom }}</td>
                                <td>{{ $classe->etudiants->count() }} étudiants</td>
                                <td><span class="badge bg-danger-subtle text-danger">{{ $classe->total_absences }}</span></td>
                                <td>
                                    <span class="text-success fw-semibold">{{ $classe->taux_presence }} %</span>
                                </td>
                                <td>
                                    <span class="text-danger fw-semibold">{{ $classe->taux_absence }} %</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Aucune donnée de classe disponible.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection