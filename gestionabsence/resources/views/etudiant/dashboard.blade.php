@extends('layouts.app') @section('content')
<div class="container-fluid px-4 py-3">
    
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h2 class="fw-bold text-dark">Bienvenue, {{ $etudiant->user->prenom }} {{ $etudiant->user->nom }} !</h2>
        <p class="text-muted mb-0">Code Étudiant : <span class="badge bg-secondary">{{ $etudiant->codePar }}</span></p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-success text-white shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Présences</h6>
                        <h3 class="fw-bold mb-0">{{ $mesPresences }}</h3>
                    </div>
                    <i class="bi bi-check-circle-fill fs-1" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-danger text-white shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Absences Justifiées/Non</h6>
                        <h3 class="fw-bold mb-0">{{ $mesAbsences }}</h3>
                    </div>
                    <i class="bi bi-x-circle-fill fs-1" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-warning text-dark shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Retards</h6>
                        <h3 class="fw-bold mb-0">{{ $mesRetards }}</h3>
                    </div>
                    <i class="bi bi-clock-fill fs-1" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar3 me-2 text-primary"></i>Cours d'aujourd'hui</h5>
        </div>
        <div class="card-body p-0">
            @if($cours->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-emoji-smile fs-2 mb-2 d-block"></i>
                    Aucun cours programmé pour aujourd'hui.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 px-3">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Matière</th>
                                <th>Enseignant</th>
                                <th>Heure</th>
                                <th>Salle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cours as $item)
                                <tr>
                                    <td class="fw-bold ps-4">{{ $item->matiere->nomMatiere }}</td>
                                    <td>{{ $item->professeur->user->prenom }} {{ $item->professeur->user->nom }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ \Carbon\Carbon::parse($item->heureDebut)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($item->heureFin)->format('H:i') }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $item->salle->nom }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection