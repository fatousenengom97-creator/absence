@extends('layouts.app')
@section('title', 'Pointage Facial')
@section('page-title', 'Pointage par reconnaissance faciale')

@push('styles')
<style>
    #video-container {
        position: relative;
        display: inline-block;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,.2);
    }
    #videoEl { display: block; width: 100%; max-width: 520px; border-radius: 12px; }
    #overlay { position: absolute; top: 0; left: 0; }
    .etudiant-card {
        cursor: pointer;
        transition: all .2s;
        border: 2px solid transparent;
    }
    .etudiant-card:hover { border-color: #2e86de; transform: translateY(-2px); }
    .etudiant-card.reconnu { border-color: #10b981; background: #ecfdf5; }
    .etudiant-card.echec   { border-color: #ef4444; background: #fef2f2; }
    #status-box {
        border-radius: 10px;
        padding: 1rem;
        font-weight: 600;
        text-align: center;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>
@endpush

@section('content')
<div class="row g-4">
    {{-- Caméra --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-camera-video me-2 text-primary"></i>
                {{ $cours->matiere->nomMatiere }} — {{ $cours->classe->nom }}
                <small class="text-muted ms-2">
                    {{ $cours->heureDebut->format('d/m/Y H:i') }} → {{ $cours->heureFin->format('H:i') }}
                </small>
            </div>
            <div class="card-body text-center">
                <div id="video-container">
                    <video id="videoEl" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                </div>

                <div id="status-box" class="mt-3 bg-light text-muted">
                    <i class="bi bi-hourglass-split pulse"></i>
                    <span id="status-text">Initialisation de la caméra…</span>
                </div>

                <div class="mt-3 d-flex gap-2 justify-content-center">
                    <button id="btnDemarrer" class="btn btn-primary" disabled>
                        <i class="bi bi-play-circle me-2"></i>Démarrer le pointage
                    </button>
                    <button id="btnArreter" class="btn btn-outline-danger d-none">
                        <i class="bi bi-stop-circle me-2"></i>Arrêter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste étudiants --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Étudiants ({{ $cours->classe->etudiants->count() }})</span>
                <span id="compteur" class="badge bg-success">0 présent(s)</span>
            </div>
            <div class="card-body p-2" style="max-height:500px;overflow-y:auto;">
                @foreach($cours->classe->etudiants as $etudiant)
                <div class="etudiant-card card mb-2 p-2" id="etudiant-{{ $etudiant->id }}"
                     data-id="{{ $etudiant->id }}"
                     data-nom="{{ $etudiant->user->full_name }}"
                     data-descriptor="{{ $etudiant->donneesBiometriques->first()?->faceVector ?? '' }}">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:36px;height:36px;background:#dbeafe;color:#1e40af;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($etudiant->user->prenom,0,1).substr($etudiant->user->nom,0,1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $etudiant->user->full_name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $etudiant->codePar }}</div>
                        </div>
                        <span class="status-badge badge badge-absent" id="badge-{{ $etudiant->id }}">Absent</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pointage manuel --}}
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-pencil me-2"></i>Pointage manuel</div>
            <div class="card-body">
                <form action="{{ route('absences.enregistrer', $cours) }}" method="POST">
                    @csrf
                    @foreach($cours->classe->etudiants as $etudiant)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="fw-semibold">{{ $etudiant->user->full_name }}</small>
                        <select name="presences[{{ $etudiant->id }}]" class="form-select form-select-sm w-auto">
                            <option value="present">Présent</option>
                            <option value="absent" selected>Absent</option>
                            <option value="retard">Retard</option>
                            <option value="justifie">Justifié</option>
                        </select>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-success w-100 mt-2">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- face-api.js --}}
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const COURS_ID    = {{ $cours->idCours }};
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').content;
const MODEL_URL   = '/models'; // place tes modèles face-api dans public/models/
let videoEl       = document.getElementById('videoEl');
let overlay       = document.getElementById('overlay');
let running       = false;
let presentCount  = 0;

// Charger les descripteurs des étudiants
const etudiants = [];
document.querySelectorAll('.etudiant-card').forEach(card => {
    const vec = card.dataset.descriptor;
    if (vec) {
        try {
            const arr = new Float32Array(JSON.parse(vec));
            etudiants.push({ id: card.dataset.id, nom: card.dataset.nom, descriptor: arr });
        } catch(e) {}
    }
});

async function loadModels() {
    setStatus('Chargement des modèles IA…', 'info');
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    setStatus('Modèles chargés. Activez la caméra.', 'success');
    document.getElementById('btnDemarrer').disabled = false;
}

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 520 } });
        videoEl.srcObject = stream;
        await new Promise(r => videoEl.addEventListener('loadedmetadata', r, { once: true }));
        overlay.width  = videoEl.videoWidth;
        overlay.height = videoEl.videoHeight;
        setStatus('Caméra active — Pointage en cours…', 'success');
        return true;
    } catch(e) {
        setStatus('Erreur caméra : ' + e.message, 'danger');
        return false;
    }
}

async function detectLoop() {
    if (!running) return;
    const ctx = overlay.getContext('2d');
    ctx.clearRect(0, 0, overlay.width, overlay.height);

    const detections = await faceapi
        .detectAllFaces(videoEl, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptors();

    if (detections.length > 0 && etudiants.length > 0) {
        const matcher = new faceapi.FaceMatcher(
            etudiants.map(e => new faceapi.LabeledFaceDescriptors(e.id, [e.descriptor])), 0.5
        );

        detections.forEach(det => {
            const match = matcher.findBestMatch(det.descriptor);
            const box   = det.detection.box;

            // Dessiner le rectangle
            ctx.strokeStyle = match.label !== 'unknown' ? '#10b981' : '#ef4444';
            ctx.lineWidth   = 2;
            ctx.strokeRect(box.x, box.y, box.width, box.height);

            const nom = match.label !== 'unknown'
                ? (etudiants.find(e => e.id === match.label)?.nom ?? 'Inconnu')
                : 'Non reconnu';

            ctx.fillStyle   = ctx.strokeStyle;
            ctx.font        = '13px Segoe UI';
            ctx.fillText(nom + (match.label !== 'unknown' ? ` (${Math.round((1-match.distance)*100)}%)` : ''), box.x, box.y - 6);

            // Marquer présent si reconnu
            if (match.label !== 'unknown') {
                const card  = document.getElementById('etudiant-' + match.label);
                const badge = document.getElementById('badge-' + match.label);
                if (card && !card.classList.contains('reconnu')) {
                    card.classList.add('reconnu');
                    badge.textContent = 'Présent';
                    badge.className   = 'status-badge badge badge-present';
                    presentCount++;
                    document.getElementById('compteur').textContent = presentCount + ' présent(s)';
                    enregistrerPresence(match.label, 1 - match.distance);
                }
            }
        });
    }

    setTimeout(detectLoop, 500);
}

async function enregistrerPresence(etudiantId, confiance) {
    await fetch(`/biometrie/cours/${COURS_ID}/traiter`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ etudiant_id: etudiantId, confiance }),
    });
}

function setStatus(msg, type='secondary') {
    const box = document.getElementById('status-box');
    const icons = { success:'bi-check-circle-fill', info:'bi-info-circle-fill', danger:'bi-exclamation-circle-fill', secondary:'bi-hourglass-split' };
    box.className = `mt-3 alert alert-${type} d-flex align-items-center gap-2`;
    box.innerHTML = `<i class="bi ${icons[type]}"></i><span>${msg}</span>`;
}

document.getElementById('btnDemarrer').addEventListener('click', async () => {
    const ok = await startCamera();
    if (!ok) return;
    running = true;
    document.getElementById('btnDemarrer').classList.add('d-none');
    document.getElementById('btnArreter').classList.remove('d-none');
    detectLoop();
});

document.getElementById('btnArreter').addEventListener('click', () => {
    running = false;
    if (videoEl.srcObject) videoEl.srcObject.getTracks().forEach(t => t.stop());
    document.getElementById('btnDemarrer').classList.remove('d-none');
    document.getElementById('btnArreter').classList.add('d-none');
    setStatus('Pointage arrêté.', 'secondary');
});

loadModels();
</script>
@endpush