@extends('layouts.app') @section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h3 class="fw-bold">Tableau de bord — Chef de Service Pédagogique</h3>
        <span class="badge bg-warning text-dark p-2">Chef de Service</span>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Étudiants</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['etudiants'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Professeurs</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['professeurs'] }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded">
                        <i class="bi bi-person-badge-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Cours Aujourd'hui</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['cours_aujourdhui'] }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded">
                        <i class="bi bi-calendar-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Absences du Mois</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['absences_mois'] }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="bi bi-bell-fill me-2"></i>Alertes Critiques : Étudiants à plus de 5 absences
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nom Complet</th>
                                    <th>Email</th>
                                    <th class="text-center">Nombre d'absences</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alertes as $alerte)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $alerte->user->name ?? 'N/A' }}</td>
                                        <td>{{ $alerte->user->email ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger p-2">{{ $alerte->nb_absences }} absences</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('chef.alertes') }}" class="btn btn-sm btn-outline-secondary">Voir détails</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Aucune alerte critique pour le moment. Tout est sous contrôle !</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection