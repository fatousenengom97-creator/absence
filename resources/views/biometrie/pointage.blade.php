@extends('layouts.app')
@section('title', 'Pointage Facial')
@section('page-title', 'Pointage — ' . ($cours->matiere?->nomMatiere ?? ''))

@push('styles')
<style>
    #video { border-radius:12px; width:100%; max-width:520px; }
    .etudiant-card { border-radius:10px; padding:10px 14px; border-left:4px solid #10b981; background:#f0fdf4; margin-bottom:8px; animation:slideIn .3s ease; }
    .inconnu-card  { border-radius:10px; padding:10px 14px; border-left:4px solid #ef4444; background:#fef2f2; margin-bottom:8px; animation:shake .5s ease; }
    @keyframes slideIn { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
    @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)} 40%{transform:translateX(8px)} 60%{transform:translateX(-8px)} 80%{transform:translateX(8px)} }
    #alerte-box { display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999; min-width:420px; text-align:center; font-size:1.1rem; font-weight:700; border-radius:12px; padding:16px 24px; box-shadow:0 4px 20px rgba(0,0,0,.25); }
    .pulse { animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
@endpush

@section('content')
<div id="alerte-box"></div>

{{-- Chargement modèles --}}
<div id="loading" class="alert alert-info text-center mb-4">
    <div class="spinner-border spinner-border-sm me-2"></div>
    Chargement des modèles de reconnaissance faciale...
</div>

<div id="zone-pointage" style="display:none;">
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background:#0B1F33;">
                <span><i class="bi bi-camera-video me-2"></i>Reconnaissance faciale</span>
                <span class="badge bg-success pulse">Actif</span>
            </div>
            <div class="card-body text-center" style="background:#111;">
                <video id="video" autoplay muted playsinline></video>
            </div>
            <div class="card-body" style="background:#1a1a1a;">
                <div class="row g-2 text-center text-white">
                    <div class="col-4">
                        <div style="font-size:.7rem;color:#94a3b8;">Matière</div>
                        <strong style="font-size:.82rem;">{{ $cours->matiere?->nomMatiere ?? '—' }}</strong>
                    </div>
                    <div class="col-4">
                        <div style="font-size:.7rem;color:#94a3b8;">Classe</div>
                        <strong style="font-size:.82rem;">{{ $cours->classe?->nom ?? '—' }}</strong>
                    </div>
                    <div class="col-4">
                        <div style="font-size:.7rem;color:#94a3b8;">Salle</div>
                        <strong style="font-size:.82rem;">{{ $cours->salle?->nom ?? '—' }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <form method="POST" action="{{ route('cours.terminer', $cours) }}" class="w-100">
                    @csrf
                    <button class="btn btn-danger w-100"
                            onclick="return confirm('Terminer le cours ?')">
                        <i class="bi bi-stop-fill me-2"></i>Terminer le cours
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-card-checklist me-2"></i>Présences</span>
                <div class="d-flex gap-2">
                    <span class="badge bg-success" id="nb-presents">0 présent(s)</span>
                    <span class="badge bg-danger" id="nb-absents">{{ $etudiants->count() }} absent(s)</span>
                </div>
            </div>
            <div class="card-body p-3" style="max-height:500px;overflow-y:auto;" id="liste-presence">
                <div class="text-center text-muted py-5" id="msg-attente">
                    <i class="bi bi-person-bounding-box fs-1 d-block mb-3"></i>
                    <div class="fw-semibold">En attente de détection...</div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Données étudiants avec descripteurs --}}
<script>
const etudiantsClasse = @json($etudiants->map(fn($e) => [
    'id'          => $e->id,
    'prenom'      => $e->user->prenom ?? '',
    'nom'         => $e->user->nom ?? '',
    'matricule'   => $e->codePar,
    'descripteur' => $e->donneesBiometriques->first()?->faceVector
        ? json_decode($e->donneesBiometriques->first()->faceVector, true)
        : null,
]));
const csrfToken    = "{{ csrf_token() }}";
const urlPresence  = "{{ route('biometrie.traiter', $cours) }}";
</script>
@endsection

@push('scripts')
<script src="{{ asset('models/face-api.min.js') }}"></script>
<script>
const video     = document.getElementById('video');
const alerteBox = document.getElementById('alerte-box');
let pointesIds  = new Set();
let nbPresents  = 0;
let labeledDescriptors = [];

// ===== SONS =====
function sonSucces() {
    try {
        const ctx = new AudioContext();
        [880, 1100].forEach((f, i) => {
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.type = 'sine'; o.frequency.value = f;
            const t = ctx.currentTime + i * .15;
            g.gain.setValueAtTime(.4, t);
            g.gain.exponentialRampToValueAtTime(.001, t + .2);
            o.connect(g); g.connect(ctx.destination);
            o.start(t); o.stop(t + .2);
        });
    } catch(e) {}
}

function sonInconnu() {
    try {
        const ctx = new AudioContext();
        [0, .25, .5].forEach(d => {
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.type = 'sawtooth'; o.frequency.value = 180;
            const t = ctx.currentTime + d;
            g.gain.setValueAtTime(.5, t);
            g.gain.exponentialRampToValueAtTime(.001, t + .2);
            o.connect(g); g.connect(ctx.destination);
            o.start(t); o.stop(t + .2);
        });
    } catch(e) {}
}

function afficherAlerte(message, type) {
    alerteBox.style.display = 'block';
    alerteBox.className = `alert alert-${type} shadow-lg`;
    alerteBox.innerHTML = type === 'success'
        ? `<i class="bi bi-check-circle-fill me-2 fs-5"></i>${message}`
        : `<i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>${message}`;
    clearTimeout(alerteBox._t);
    alerteBox._t = setTimeout(() => alerteBox.style.display = 'none', 4000);
}

function ajouterPresent(etudiant) {
    document.getElementById('msg-attente')?.remove();
    nbPresents++;
    document.getElementById('nb-presents').textContent = nbPresents + ' présent(s)';
    const absents = Math.max(0, etudiantsClasse.length - nbPresents);
    document.getElementById('nb-absents').textContent = absents + ' absent(s)';
    const heure = new Date().toLocaleTimeString('fr-FR');
    const div = document.createElement('div');
    div.className = 'etudiant-card';
    div.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                 style="width:42px;height:42px;">
                ${etudiant.prenom[0]}${etudiant.nom[0]}
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">${etudiant.prenom} ${etudiant.nom}</div>
                <small class="text-muted">${etudiant.matricule}</small>
            </div>
            <div class="text-end">
                <span class="badge bg-success">✅ Présent</span>
                <div style="font-size:.7rem;color:#6b7280;">${heure}</div>
            </div>
        </div>`;
    document.getElementById('liste-presence').prepend(div);
}

function ajouterInconnu() {
    const div = document.createElement('div');
    div.className = 'inconnu-card';
    div.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-person-x text-danger fs-2 flex-shrink-0"></i>
            <div>
                <div class="fw-semibold text-danger">⚠️ Visage non reconnu</div>
                <small class="text-muted">${new Date().toLocaleTimeString('fr-FR')}</small>
            </div>
        </div>`;
    document.getElementById('liste-presence').prepend(div);
    setTimeout(() => div.remove(), 5000);
}

async function enregistrerPresence(etudiant) {
    try {
        await fetch(urlPresence, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ etudiant_id: etudiant.id, confiance: 0.9 })
        });
    } catch(e) { console.error(e); }
}

async function chargerModeles() {
    const MODEL_URL = '/models';
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);

    // Préparer les descripteurs des étudiants
    etudiantsClasse.forEach(e => {
        if (e.descripteur && e.descripteur.length === 128) {
            const descriptor = new Float32Array(e.descripteur);
            labeledDescriptors.push(
                new faceapi.LabeledFaceDescriptors(String(e.id), [descriptor])
            );
        }
    });

    console.log(`${labeledDescriptors.length} visage(s) chargé(s) pour comparaison`);

    document.getElementById('loading').style.display = 'none';
    document.getElementById('zone-pointage').style.display = 'block';
    await demarrerCamera();
}

async function demarrerCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 520, height: 390, facingMode: 'user' }
        });
        video.srcObject = stream;
        await video.play();
        setInterval(scanner, 1500);
    } catch(e) {
        afficherAlerte('❌ Caméra inaccessible : ' + e.message, 'danger');
    }
}

async function scanner() {
    if (!video.videoWidth || labeledDescriptors.length === 0) return;

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) return;

    const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
    const match = faceMatcher.findBestMatch(detection.descriptor);

    if (match.label !== 'unknown') {
        const etudiantId = parseInt(match.label);
        if (!pointesIds.has(etudiantId)) {
            pointesIds.add(etudiantId);
            const etudiant = etudiantsClasse.find(e => e.id === etudiantId);
            if (etudiant) {
                sonSucces();
                afficherAlerte(`✅ ${etudiant.prenom} ${etudiant.nom} — Présence confirmée !`, 'success');
                ajouterPresent(etudiant);
                await enregistrerPresence(etudiant);
            }
        }
    } else {
        sonInconnu();
        afficherAlerte('⛔ Visage non reconnu dans cette classe !', 'danger');
        ajouterInconnu();
    }
}

chargerModeles();
</script>
@endpush