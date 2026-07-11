@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('biometrie.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <!-- Infos Étudiant -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body text-center p-4">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-graduate fa-2x text-secondary"></i>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $etudiant->full_name }}</h4>
                    <p class="text-muted small mb-3">Code: {{ $etudiant->codePar }}</p>
                    <span class="badge bg-primary px-3 py-2">{{ $etudiant->classe?->nom ?? 'Non assignée' }}</span>
                </div>
            </div>
        </div>

        <!-- Zone de Capture / Caméra -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Enrôlement Biométrique</h5>
                    
                    <div class="bg-dark text-white rounded-3 d-flex flex-column align-items-center justify-content-center position-relative mb-4" style="height: 350px;">
                        <i class="fas fa-camera fa-3x text-muted mb-3" id="camera-icon"></i>
                        <p class="text-muted mb-0" id="camera-status">Prêt pour l'enrôlement</p>
                        
                        <!-- Zone de preview de simulation (Si vous utilisez une webcam web) -->
                        <video id="webcam" autocomplete="off" class="d-none w-100 h-100 rounded-3 position-absolute" style="object-fit: cover;" autoplay playsinline></video>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button id="btn-ouvrir-camera" class="btn btn-outline-primary px-4">
                            <i class="fas fa-video me-2"></i>Démarrer la caméra
                        </button>
                        <button id="btn-capturer" class="btn btn-success px-4" disabled>
                            <i class="fas fa-fingerprint me-2"></i>Capturer le visage
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Comment as-tu prévu de lancer ton script Python ? 
    // Si c'est via une requête AJAX qui déclenche Python sur le serveur :
    document.getElementById('btn-ouvrir-camera').addEventListener('click', function() {
        const statusText = document.getElementById('camera-status');
        statusText.innerText = "Lancement de la reconnaissance faciale...";
        
        // C'est ici qu'on placera le fetch() vers la route Laravel qui exécute ton script Python !
        alert("Caméra prête ! Nous allons lier le script Python ici.");
        document.getElementById('btn-capturer').removeAttribute('disabled');
    });
</script>
@endsection