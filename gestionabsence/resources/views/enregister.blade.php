@extends('layouts.app')
@section('title', 'Enregistrement biométrique')
@section('page-title', 'Enregistrement du visage')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header">
        <i class="bi bi-person-bounding-box me-2 text-primary"></i>
        Enregistrement biométrique — {{ $etudiant->user->full_name }}
        <small class="text-muted ms-2">({{ $etudiant->codePar }})</small>
    </div>
    <div class="card-body text-center">

        @if($biometrie)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Données biométriques déjà enregistrées le {{ $biometrie->dateEnregistre->format('d/m/Y H:i') }}.
            Vous pouvez les mettre à jour ci-dessous.
        </div>
        @endif

        <div id="video-container" style="position:relative;display:inline-block;margin-bottom:1rem;">
            <video id="videoEl" width="400" height="300" autoplay muted style="border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.15);"></video>
            <canvas id="overlay" style="position:absolute;top:0;left:0;"></canvas>
        </div>

        <canvas id="snapshot" width="400" height="300" style="display:none;"></canvas>

        <div id="status" class="alert alert-secondary mb-3">
            <i class="bi bi-camera me-2"></i>Activez la caméra pour commencer.
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <button id="btnCamera" class="btn btn-primary">
                <i class="bi bi-camera-video me-2"></i>Activer la caméra
            </button>
            <button id="btnCapture" class="btn btn-success d-none">
                <i class="bi bi-camera me-2"></i>Capturer
            </button>
            <button id="btnSauvegarder" class="btn btn-warning d-none">
                <i class="bi bi-save me-2"></i>Sauvegarder
            </button>
        </div>

        <img id="previewImg" class="mt-3 d-none" style="border-radius:10px;max-width:200px;" alt="Aperçu">
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL   = '/models';
const SAVE_URL    = "{{ route('biometrie.sauvegarder', $etudiant) }}";
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').content;

let videoEl    = document.getElementById('videoEl');
let overlay    = document.getElementById('overlay');
let snapshot   = document.getElementById('snapshot');
let capturedDescriptor = null;
let capturedPhoto      = null;

overlay.width  = 400;
overlay.height = 300;

async function init() {
    setStatus('Chargement des modèles…', 'info');
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    setStatus('Modèles prêts. Activez la caméra.', 'success');
    document.getElementById('btnCamera').disabled = false;
}

document.getElementById('btnCamera').addEventListener('click', async () => {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    videoEl.srcObject = stream;
    document.getElementById('btnCamera').classList.add('d-none');
    document.getElementById('btnCapture').classList.remove('d-none');
    setStatus('Positionnez votre visage face à la caméra, puis capturez.', 'info');
    detectFace();
});

async function detectFace() {
    const ctx = overlay.getContext('2d');
    ctx.clearRect(0, 0, overlay.width, overlay.height);
    const det = await faceapi.detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
    if (det) {
        const box = det.detection.box;
        ctx.strokeStyle = '#10b981'; ctx.lineWidth = 2;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
        ctx.fillStyle = '#10b981'; ctx.font = '12px Segoe UI';
        ctx.fillText('Visage détecté', box.x, box.y - 5);
    }
    requestAnimationFrame(detectFace);
}

document.getElementById('btnCapture').addEventListener('click', async () => {
    setStatus('Analyse du visage…', 'info');
    const det = await faceapi
        .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!det) {
        setStatus('Aucun visage détecté. Réessayez.', 'danger');
        return;
    }

    capturedDescriptor = Array.from(det.descriptor);

    // Prendre la photo
    const ctx = snapshot.getContext('2d');
    ctx.drawImage(videoEl, 0, 0, 400, 300);
    capturedPhoto = snapshot.toDataURL('image/jpeg', 0.85);

    // Afficher l'aperçu
    document.getElementById('previewImg').src = capturedPhoto;
    document.getElementById('previewImg').classList.remove('d-none');
    document.getElementById('btnSauvegarder').classList.remove('d-none');

    setStatus('Visage capturé avec succès ! Cliquez sur Sauvegarder.', 'success');
});

document.getElementById('btnSauvegarder').addEventListener('click', async () => {
    if (!capturedDescriptor || !capturedPhoto) return;
    setStatus('Sauvegarde en cours…', 'info');

    const res = await fetch(SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ face_vector: JSON.stringify(capturedDescriptor), photo: capturedPhoto }),
    });

    const data = await res.json();
    if (data.success) {
        setStatus('✅ ' + data.message, 'success');
        document.getElementById('btnSauvegarder').disabled = true;
    } else {
        setStatus('Erreur : ' + (data.message ?? 'Réessayez'), 'danger');
    }
});

function setStatus(msg, type='secondary') {
    const el = document.getElementById('status');
    el.className = `alert alert-${type}`;
    el.innerHTML = msg;
}

init();
</script>
@endpush