@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Gestion Biométrique</h1>
            <p class="text-muted mb-0">Contrôle des caméras, suivi des empreintes faciales et états du système.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cpu me-2 text-primary"></i> État du module Python/OpenCV</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Statut du Serveur :</span>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">🟢 Opérationnel</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Étudiants enrôlés :</span>
                        <span class="fw-bold">142</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Précision du Modèle :</span>
                        <span class="text-success fw-bold">98.4%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-camera-video me-2 text-primary"></i> Flux de Reconnaissance Faciale</h5>
                </div>
                <div class="card-body py-5 text-center text-muted">
                    <div class="p-4 mx-auto mb-3 bg-light rounded d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                        <i class="bi bi-shield-lock-fill fs-1 text-primary"></i>
                    </div>
                    <p class="fw-bold text-dark mb-1">Console de supervision de l'enrôlement</p>
                    <p class="small text-muted mb-0">Sélectionnez un étudiant dans la liste des utilisateurs pour lancer la capture de son visage.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection