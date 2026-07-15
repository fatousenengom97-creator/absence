@extends('layouts.app')
@section('title', 'Dashboard Chef de Service')
@section('page-title', 'Tableau de bord — Chef de Service')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-building fs-1 text-primary"></i>
                <h4 class="mt-2 fw-bold">{{ $totalClasses }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Classes</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-person-badge fs-1 text-success"></i>
                <h4 class="mt-2 fw-bold">{{ $totalProfesseurs }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Professeurs</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body py-4">
                <i class="bi bi-calendar-week fs-1 text-warning"></i>
                <h4 class="mt-2 fw-bold">{{ $totalEDT }}</h4>
                <small class="text-muted text-uppercase fw-semibold">Créneaux planifiés</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-week fs-1 text-primary mb-3 d-block"></i>
                <h5 class="fw-bold">Emplois du temps</h5>
                <p class="text-muted">Créer et gérer les emplois du temps par classe</p>
                <!-- Modifié ici pour utiliser 'chef.edt.index' présent dans tes routes -->
                <a href="{{ route('chef.edt.index') }}" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-right me-2"></i>Gérer les EDT
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-grid fs-1 text-success mb-3 d-block"></i>
                <h5 class="fw-bold">Disponibilité des salles</h5>
                <p class="text-muted">Voir les salles occupées et disponibles</p>
                <!-- Modifié ici pour utiliser 'chef.salles' présent dans tes routes -->
                <a href="{{ route('chef.salles') }}" class="btn btn-success px-4">
                    <i class="bi bi-arrow-right me-2"></i>Voir les salles
                </a>
            </div>
        </div>
    </div>
</div>
@endsection