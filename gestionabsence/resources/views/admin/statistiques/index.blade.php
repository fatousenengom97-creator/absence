@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Statistiques Globales</h1>
            <p class="text-muted mb-0">Visualisez les rapports de présence et d'absences de l'établissement.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white rounded">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 rounded text-primary me-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Taux de Présence Global</h6>
                        <h3 class="mb-0 fw-bold">94.2%</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white rounded">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 rounded text-danger me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Absences non justifiées</h6>
                        <h3 class="mb-0 fw-bold">12</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white rounded">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 rounded text-success me-3">
                        <i class="bi bi-building fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Classes Actives</h6>
                        <h3 class="mb-0 fw-bold">8</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart-line me-2 text-primary"></i> Évolution des absences par filière</h5>
        </div>
        <div class="card-body py-5 text-center text-muted">
            <i class="bi bi-graph-up fs-1 mb-3 text-secondary d-block"></i>
            <p class="mb-0">Le graphique d'analyse des pointages et des présences s'affichera ici.</p>
        </div>
    </div>
</div>
@endsection