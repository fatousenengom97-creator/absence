@extends('layouts.app')
@section('title', 'Enregistrement biométrique')
@section('page-title', 'Enregistrement biométrique')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-camera me-2 text-primary"></i>
        Enregistrement facial — {{ $etudiant->user->prenom }} {{ $etudiant->user->nom }}
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
                <small class="text-muted d-block">Statut biométrique</small>
                @if($biometrie)
                    <span class="badge bg-success">Déjà enregistré le {{ $biometrie->dateEnregistre?->format('d/m/Y') }}</span>
                @else
                    <span class="badge bg-warning text-dark">Non enregistré</span>
                @endif
            </div>
        </div>

        {{-- Zone caméra --}}
        <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
                <video id="video" width="480" height="360" autoplay muted
                       class="rounded-3 border" style="background:#000;"></video>
                <canvas id="canvas" width="480" height="360" class="d-none"></canvas>
                <div id="overlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                     style="background:rgba(0,0,0,.5);border-radius:12px;display:none!important;">
                    <div class="text-white text-center">
                        <i class="bi bi-camera fs-1"></i>
                        <div>Cliquez pour démarrer</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aperçu photo --}}
        <div id="apercu-container" class="text-center mb-4 d-none">
            <img id="apercu" class="rounded-3 border" style="max-width:480px;">
            <div class="mt-2 text-muted small">Aperçu de la photo capturée</div>
        </div>

        {{-- Messages --}}
        <div id="message" class="alert d-none mb-3"></div>

        {{-- Boutons --}}
        <div class="d-flex gap-3 justify-content-center">
            <button id="btn-demarrer" class="btn btn-outline-primary" onclick="demarrerCamera()">
                <i class="bi bi-camera me-2"></i>Démarrer la caméra
            </button>
            <button id="btn-capturer" class="btn btn-success d-none" onclick="capturerPhoto()">
                <i class="bi bi-camera-fill me-2"></i>Capturer
            </button>
            <button id="btn-enregistrer" class="btn btn-primary d-none" onclick="enregistrer()">
                <i class="bi bi-save me-2"></i>Enregistrer
            </button>
            <button id="btn-reprendre" class="btn btn-outline-secondary d-none" onclick="reprendre()">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Reprendre
            </button>
        </div>
    </div>
</div>

<div class="text-center">
    <a href="{{ route('biometrie.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Retour à la liste
    </a>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
let stream = null;
let photoData = null;
const video   = document.getElementById('video');
const canvas  = document.getElementById('canvas');
const apercu  = document.getElementById('apercu');
const message = document.getElementById('message');

function afficherMessage(texte, type = 'info') {
    message.className = `alert alert-${type}`;
    message.textContent = texte;
    message.classList.remove('d-none');
}

async function demarrerCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        document.getElementById('btn-demarrer').classList.add('d-none');
        document.getElementById('btn-capturer').classList.remove('d-none');
        afficherMessage('Caméra démarrée. Positionnez votre visage et cliquez sur Capturer.', 'info');
    } catch (e) {
        afficherMessage('Impossible d\'accéder à la caméra : ' + e.message, 'danger');
    }
}

function capturerPhoto() {
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, 480, 360);
    photoData = canvas.toDataURL('image/jpeg');
    apercu.src = photoData;
    document.getElementById('apercu-container').classList.remove('d-none');
    document.getElementById('btn-capturer').classList.add('d-none');
    document.getElementById('btn-enregistrer').classList.remove('d-none');
    document.getElementById('btn-reprendre').classList.remove('d-none');
    afficherMessage('Photo capturée. Vérifiez l\'aperçu et cliquez sur Enregistrer.', 'success');
}

function reprendre() {
    photoData = null;
    document.getElementById('apercu-container').classList.add('d-none');
    document.getElementById('btn-capturer').classList.remove('d-none');
    document.getElementById('btn-enregistrer').classList.add('d-none');
    document.getElementById('btn-reprendre').classList.add('d-none');
    message.classList.add('d-none');
}

async function enregistrer() {
    if (!photoData) return;

    document.getElementById('btn-enregistrer').disabled = true;
    afficherMessage('Enregistrement en cours...', 'info');

    try {
        const response = await fetch('{{ route("biometrie.sauvegarder", $etudiant) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                face_vector: btoa(photoData.substring(0, 100)), // vecteur simplifié
                photo: photoData,
            })
        });

        const data = await response.json();
        if (data.success) {
            afficherMessage('✅ ' + data.message, 'success');
            if (stream) stream.getTracks().forEach(t => t.stop());
            setTimeout(() => window.location.href = '{{ route("biometrie.index") }}', 2000);
        } else {
            afficherMessage('❌ Erreur : ' + (data.message ?? 'Inconnue'), 'danger');
            document.getElementById('btn-enregistrer').disabled = false;
        }
    } catch (e) {
        afficherMessage('❌ Erreur réseau : ' + e.message, 'danger');
        document.getElementById('btn-enregistrer').disabled = false;
    }
}
</script>
@endpush