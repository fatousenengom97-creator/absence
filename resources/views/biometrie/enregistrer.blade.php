@extends('layouts.app')
@section('title', 'Enregistrement biométrique')
@section('page-title', 'Enregistrement biométrique')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header">
        <i class="bi bi-camera me-2 text-primary"></i>
        Enregistrement — {{ $etudiant->user->prenom }} {{ $etudiant->user->nom }}
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <small class="text-muted d-block">Code étudiant</small>
                <strong>{{ $etudiant->codePar }}</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Classe</small>
                <strong>{{ $etudiant->inscriptionActuelle?->classe?->nom ?? '—' }}</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Statut</small>
                @if($biometrie)
                    <span class="badge bg-success">Déjà enregistré</span>
                @else
                    <span class="badge bg-warning text-dark">Non enregistré</span>
                @endif
            </div>
        </div>

        {{-- Zone caméra --}}
        <div id="zone-camera">
            <div class="text-center mb-3">
                <div class="position-relative d-inline-block">
                    <video id="video" width="480" height="360" autoplay muted class="rounded-3 border"></video>
                </div>
            </div>

            <div id="statut-detection" class="alert alert-info text-center mb-3">
                <i class="bi bi-camera me-2"></i>Regardez l'objectif et cliquez sur "Capturer et enregistrer".
            </div>

            <div class="d-flex gap-3 justify-content-center">
                <button id="btn-capturer" class="btn btn-success btn-lg" onclick="capturerEtEnregistrer()">
                    <i class="bi bi-camera-fill me-2"></i>Capturer et enregistrer
                </button>
                <a href="{{ route('biometrie.index') }}" class="btn btn-outline-secondary btn-lg">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const video = document.getElementById('video');
let streaming = false;

// Démarrage immédiat de la caméra
async function demarrerCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            streaming = true;
        };
    } catch(e) {
        document.getElementById('statut-detection').className = 'alert alert-danger text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '❌ Caméra inaccessible : ' + e.message;
    }
}

async function capturerEtEnregistrer() {
    if (!streaming) return;

    document.getElementById('btn-capturer').disabled = true;
    document.getElementById('statut-detection').className = 'alert alert-warning text-center mb-3';
    document.getElementById('statut-detection').innerHTML = '⏳ Capture et analyse en cours...';

    // Créer un canvas temporaire pour prendre la photo
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width  = 640;
    tmpCanvas.height = 480;
    tmpCanvas.getContext('2d').drawImage(video, 0, 0, 640, 480);

    // Capture de la photo en qualité JPEG standard pour traitement serveur
    const photoBase64 = tmpCanvas.toDataURL('image/jpeg', 0.8);

    try {
        // Envoi de la photo à Laravel — c'est le contrôleur PHP qui appelle
        // ensuite le serveur Python pour extraire le vecteur facial
        const res = await fetch("{{ route('biometrie.sauvegarder', $etudiant) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                photo: photoBase64
            })
        });

        const data = await res.json();
        if (data.success) {
            document.getElementById('statut-detection').className = 'alert alert-success text-center mb-3';
            document.getElementById('statut-detection').innerHTML = '✅ Enregistrement réussi ! Redirection...';
            setTimeout(() => window.location.href = "{{ route('biometrie.index') }}", 1500);
        } else {
            throw new Error(data.message ?? 'Erreur lors de la sauvegarde.');
        }
    } catch(e) {
        document.getElementById('statut-detection').className = 'alert alert-danger text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '❌ Erreur : ' + e.message;
        document.getElementById('btn-capturer').disabled = false;
    }
}

// Initialisation au chargement de la page
demarrerCamera();
</script>
@endpush