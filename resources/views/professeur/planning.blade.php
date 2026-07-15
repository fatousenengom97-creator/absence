@extends('layouts.app')

@section('content')
<div class="container mt-4">
    
    <!-- Zone d'affichage des messages de succès ou d'erreur -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Mon Emploi du Temps de la Semaine</h5>
        </div>
        <div class="card-body p-4">

            @if(empty($mesSeances) || $mesSeances->isEmpty())
                <div class="text-center py-5 bg-light rounded border">
                    <span class="fs-1 text-muted"><i class="bi bi-calendar-x"></i></span>
                    <h5 class="mt-3 text-secondary">Aucun cours planifié pour vous cette semaine.</h5>
                </div>
            @else
                <div class="timeline">
                    @foreach($mesSeances as $date => $seancesDuJour)
                        <div class="mb-4">
                            <!-- Affichage de la date en français (Ex: Lundi 15 Juin) -->
                            <h5 class="text-primary border-bottom pb-2 fw-bold">
                                <i class="bi bi-calendar-event me-2"></i>{{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                            </h5>
                            
                            <div class="row">
                                @foreach($seancesDuJour as $seance)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-start border-primary border-4 shadow-sm h-100">
                                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-bold text-dark mb-0">
                                                            {{ $seance->nomCours ?? $seance->nom_cours }}
                                                        </h6>
                                                        <span class="badge bg-info text-white">
                                                            {{ $seance->classe->nomClasse ?? $seance->classe->nom ?? $seance->classe ?? 'Classe' }}
                                                        </span>
                                                    </div>
                                                    
                                                    <p class="mb-1 text-muted small">
                                                        <i class="bi bi-geo-alt-fill me-1"></i><strong>Salle :</strong> {{ $seance->salle }}
                                                    </p>
                                                    
                                                    <div class="bg-light p-2 rounded text-center small fw-bold text-danger mt-2 mb-3">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Horaires : {{ substr($seance->heureDebut ?? $seance->heure_debut, 0, 5) }} — {{ substr($seance->heureFin ?? $seance->heure_fin, 0, 5) }}
                                                    </div>
                                                </div>

                                                <!-- SECTION ACTIONS DYNAMIQUES (DÉMARRAGE / POINTAGE / CLÔTURE) -->
                                                <div class="mt-auto pt-2 border-top">
                                                    @if(($seance->statut ?? 'planifie') === 'planifie')
                                                        <!-- Le cours n'a pas encore commencé -->
                                                        <form action="{{ route('prof.cours.demarrer', $seance->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm">
                                                                <i class="bi bi-play-fill me-1"></i> Démarrer le cours
                                                            </button>
                                                        </form>

                                                    @elseif($seance->statut === 'en_cours')
                                                        <!-- Le cours est actuellement actif -->
                                                        <div class="d-flex flex-column gap-2">
                                                            @if($seance->pointageEstOuvert())
                                                                <span class="badge bg-success p-2 text-wrap shadow-sm">
                                                                    <i class="bi bi-camera-video me-1"></i> Pointage ouvert (Reconnaissance faciale active)
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary p-2 text-wrap shadow-sm">
                                                                    <i class="bi bi-lock-fill me-1"></i> Pointage fermé (Délai de 30 min expiré)
                                                                </span>
                                                            @endif

                                                            <!-- Bouton de clôture définitive -->
                                                            <form action="{{ route('prof.cours.cloturer', $seance->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm">
                                                                    <i class="bi bi-stop-fill me-1"></i> Clôturer le cours
                                                                </button>
                                                            </form>
                                                        </div>

                                                    @elseif($seance->statut === 'termine')
                                                        <!-- Le cours est fini -->
                                                        <span class="badge bg-dark w-100 p-2 text-wrap">
                                                            <i class="bi bi-check2-all me-1"></i> Cours Terminé et Archivé
                                                        </span>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@endsection