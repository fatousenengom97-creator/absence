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

        {{-- Chargement modèles --}}
        <div id="loading" class="text-center py-4">
            <div class="spinner-border text-primary mb-3"></div>
            <div class="fw-semibold">Chargement des modèles de reconnaissance...</div>
            <small class="text-muted">Veuillez patienter</small>
        </div>

        {{-- Zone caméra --}}
        <div id="zone-camera" style="display:none;">
            <div class="text-center mb-3">
                <div class="position-relative d-inline-block">
                    <video id="video" width="480" height="360" autoplay muted
                           class="rounded-3 border"></video>
                    <canvas id="canvas-overlay" width="480" height="360"
                            class="position-absolute top-0 start-0 rounded-3"
                            style="pointer-events:none;"></canvas>
                </div>
            </div>

            <div id="statut-detection" class="alert alert-info text-center mb-3">
                <i class="bi bi-camera me-2"></i>Positionnez votre visage dans le cadre
            </div>

            <div class="d-flex gap-3 justify-content-center">
                <button id="btn-capturer" class="btn btn-success btn-lg" disabled onclick="capturerEtEnregistrer()">
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
<script src="{{ asset('models/face-api.min.js') }}"></script>
<script>
const video  = document.getElementById('video');
const canvas = document.getElementById('canvas-overlay');
const ctx    = canvas.getContext('2d');
let detectInterval = null;
let visageDetecte  = false;
let descripteurActuel = null;

async function chargerModeles() {
    const MODEL_URL = '/models';
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    document.getElementById('loading').style.display = 'none';
    document.getElementById('zone-camera').style.display = 'block';
    await demarrerCamera();
}

async function demarrerCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            detectInterval = setInterval(detecterVisage, 200);
        };
    } catch(e) {
        document.getElementById('statut-detection').className = 'alert alert-danger text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '❌ Caméra inaccessible : ' + e.message;
    }
}

async function detecterVisage() {
    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (detection) {
        // Dessiner le cadre
        const box = detection.detection.box;
        ctx.strokeStyle = '#10b981';
        ctx.lineWidth = 3;
        ctx.strokeRect(box.x, box.y, box.width, box.height);

        visageDetecte = true;
        descripteurActuel = detection.descriptor;
        document.getElementById('btn-capturer').disabled = false;
        document.getElementById('statut-detection').className = 'alert alert-success text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '✅ Visage détecté — cliquez sur "Capturer"';
    } else {
        visageDetecte = false;
        descripteurActuel = null;
        document.getElementById('btn-capturer').disabled = true;
        document.getElementById('statut-detection').className = 'alert alert-info text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '🔍 Positionnez votre visage dans le cadre';
    }
}

async function capturerEtEnregistrer() {
    if (!visageDetecte || !descripteurActuel) return;

    clearInterval(detectInterval);
    document.getElementById('btn-capturer').disabled = true;
    document.getElementById('statut-detection').innerHTML = '⏳ Enregistrement en cours...';

    // Capturer photo
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width  = video.videoWidth;
    tmpCanvas.height = video.videoHeight;
    tmpCanvas.getContext('2d').drawImage(video, 0, 0);
    const photoBase64 = tmpCanvas.toDataURL('image/jpeg', 0.8);

    // Convertir le descripteur en JSON
    const descripteurJson = JSON.stringify(Array.from(descripteurActuel));

    try {
        const res = await fetch("{{ route('biometrie.sauvegarder', $etudiant) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                face_vector: descripteurJson,
                photo: photoBase64,
            })
        });

        const data = await res.json();
        if (data.success) {
            document.getElementById('statut-detection').className = 'alert alert-success text-center mb-3';
            document.getElementById('statut-detection').innerHTML = '✅ Enregistrement réussi ! Redirection...';
            setTimeout(() => window.location.href = "{{ route('biometrie.index') }}", 2000);
        } else {
            throw new Error(data.message ?? 'Erreur inconnue');
        }
    } catch(e) {
        document.getElementById('statut-detection').className = 'alert alert-danger text-center mb-3';
        document.getElementById('statut-detection').innerHTML = '❌ Erreur : ' + e.message;
        detectInterval = setInterval(detecterVisage, 200);
        document.getElementById('btn-capturer').disabled = false;
    }
}

chargerModeles();
</script>
@endpush