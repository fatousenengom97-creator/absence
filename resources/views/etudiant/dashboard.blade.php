@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- En-tête de bienvenue -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Bonjour, {{ $etudiant->prenom ?? auth()->user()->name }} !</h1>
            <p class="text-muted">Ravi de vous revoir. Voici un aperçu de votre situation aujourd'hui.</p>
        </div>
    </div>

    <!-- Section Statistiques -->
    <div class="row g-3 mb-4">
        <!-- Carte Classe -->
        <div class="col-md-4">
            <div class="card h-100 border-start border-primary border-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary p-3 rounded">
                            <i class="bi bi-mortarboard fs-3"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-subtitle text-muted mb-1">Votre Classe</h6>
                            <h5 class="card-title mb-0">{{ $classe->nomClasse ?? 'Non assignée' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Absences non justifiées -->
        <div class="col-md-4">
            <div class="card h-100 border-start border-danger border-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger bg-opacity-10 text-danger p-3 rounded">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-subtitle text-muted mb-1">Absences Signalées</h6>
                            <h5 class="card-title mb-0">{{ $totalAbsences }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Absences Justifiées -->
        <div class="col-md-4">
            <div class="card h-100 border-start border-success border-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 text-success p-3 rounded">
                            <i class="bi bi-file-earmark-check fs-3"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-subtitle text-muted mb-1">Absences Justifiées</h6>
                            <h5 class="card-title mb-0">{{ $totalJustifies }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Cours -->
    <div class="row g-4">
        <!-- Cours d'aujourd'hui -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Cours d'aujourd'hui</h5>
                </div>
                <div class="card-body p-0">
                    @if(empty($coursAujourdhui) || $coursAujourdhui->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                            Aucun cours programmé pour aujourd'hui.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Heure</th>
                                        <th>Matière</th>
                                        <th>Salle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coursAujourdhui as $cours)
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                {{ \Carbon\Carbon::parse($cours->heureDebut)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($cours->heureFin)->format('H:i') }}
                                            </td>
                                            <td>
                                                <span class="d-block fw-semibold">{{ $cours->matiere->nomMatiere }}</span>
                                                <small class="text-muted">{{ $cours->professeur->user->name ?? 'Professeur' }}</small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $cours->salle->nomSalle }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Prochains cours de la semaine -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-calendar-week me-2 text-success"></i>Prochains cours de la semaine</h5>
                </div>
                <div class="card-body p-0">
                    {{-- CORRECTION FAITE ICI : $prochainsCoours est devenu $prochainsCours --}}
                    @if(empty($prochainsCours) || $prochainsCours->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar fs-2 d-block mb-2"></i>
                            Aucun autre cours planifié pour le reste de la semaine.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Heure</th>
                                        <th>Matière</th>
                                        <th>Salle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- CORRECTION FAITE ICI : $prochainsCoours est devenu $prochainsCours --}}
                                    @foreach($prochainsCours as $cours)
                                        <tr>
                                            <td>
                                                <span class="d-block fw-semibold">{{ \Carbon\Carbon::parse($cours->heureDebut)->translatedFormat('l d F') }}</span>
                                                <small class="text-primary fw-bold">
                                                    {{ \Carbon\Carbon::parse($cours->heureDebut)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($cours->heureFin)->format('H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="d-block fw-semibold">{{ $cours->matiere->nomMatiere }}</span>
                                                <small class="text-muted">{{ $cours->professeur->user->name ?? 'Professeur' }}</small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $cours->salle->nomSalle }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection