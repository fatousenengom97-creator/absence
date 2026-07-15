@extends('layouts.app')
@section('title', 'Pointage Facial')
@section('page-title', 'Session de Pointage Facial')

@section('content')
<div class="mb-3">
    <a href="{{ route('cours.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour aux cours
    </a>
</div>

{{-- Alerte étudiant inconnu --}}
<div id="alerte-inconnu" class="alert alert-danger d-none mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
    <strong id="nom-inconnu">ÉTUDIANT INCONNU</strong> — Non inscrit dans cette classe !
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-camera-video me-2"></i>Caméra de Reconnaissance</span>
                <span id="statut-scan" class="badge bg-success animate-pulse">En cours...</span>
            </div>
            <div class="card-body bg-light d-flex flex-column align-items-center justify-content-center" style="min-height:380px;">
                <div class="position-relative border rounded bg-dark w-100 overflow-hidden mb-3" style="max-width:500px;aspect-ratio:4/3;">
                    <div id="camera-placeholder" class="position-absolute top-50 start-50 translate-middle text-white text-center">
                        <i class="bi bi-camera fs-1 d-block mb-2 text-muted"></i>
                        <span class="text-muted small">Initialisation...</span>
                    </div>
                    <video id="video-feed" autoplay muted playsinline class="w-100 h-100" style="object-fit:cover;"></video>
                </div>
                <div class="alert alert-info w-100 text-start mb-0">
                    <strong>{{ $cours->matiere->nomMatiere ?? '—' }}</strong><br>
                    <small>
                        <i class="bi bi-people me-1"></i>{{ $cours->classe->nom ?? '—' }}
                        &nbsp;|&nbsp;
                        <i class="bi bi-geo-alt me-1"></i>{{ $cours->salle->nom ?? '—' }}
                    </small>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button id="btn-scan" class="btn btn-primary w-50">
                    <i class="bi bi-arrow-repeat me-1"></i>Scanner
                </button>
                <form method="POST" action="{{ route('cours.terminer', $cours) }}" class="w-50">
                    @csrf
                    <button class="btn btn-danger w-100">
                        <i class="bi bi-stop-fill me-1"></i>Clôturer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-card-checklist me-2"></i>Liste de présence</span>
                <span id="counter" class="badge bg-primary">0 Présent</span>
            </div>
            <div class="card-body p-0" style="max-height:480px;overflow-y:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Photo</th>
                            <th>Étudiant</th>
                            <th>Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody id="presence-list">
                        <tr id="empty-row">
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-person-bounding-box fs-2 d-block mb-2"></i>
                                En attente de détection...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse { 0%,100%{opacity:.5} 50%{opacity:1} }
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes shake {
        0%,100%{transform:translateX(0)}
        20%{transform:translateX(-10px)}
        40%{transform:translateX(10px)}
        60%{transform:translateX(-10px)}
        80%{transform:translateX(10px)}
    }
    .shake { animation: shake .5s ease; }
</style>

<script>
// ===== SONS =====
function sonSucces() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        // Bip aigu court = succès
        [880, 1100].forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.3, ctx.currentTime + i * 0.12);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.12 + 0.1);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(ctx.currentTime + i * 0.12);
            osc.stop(ctx.currentTime + i * 0.12 + 0.1);
        });
    } catch(e) {}
}

function sonInconnu() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        // Alarme grave répétée = inconnu
        [0, 0.3, 0.6].forEach(delay => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.value = 200;
            gain.gain.setValueAtTime(0.5, ctx.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + delay + 0.25);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.25);
        });
    } catch(e) {}
}

// ===== LOGIQUE PRINCIPALE =====
document.addEventListener('DOMContentLoaded', function () {
    const video        = document.getElementById('video-feed');
    const placeholder  = document.getElementById('camera-placeholder');
    const presenceList = document.getElementById('presence-list');
    const emptyRow     = document.getElementById('empty-row');
    const counter      = document.getElementById('counter');
    const alerteDiv    = document.getElementById('alerte-inconnu');
    const nomInconnu   = document.getElementById('nom-inconnu');
    let pointes        = new Set();
    let scanInterval;

    // Démarrer caméra
    navigator.mediaDevices?.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            video.srcObject = stream;
            if (placeholder) placeholder.classList.add('d-none');
            scanInterval = setInterval(scanner, 3000);
        })
        .catch(() => {
            if (placeholder) placeholder.innerHTML = '<i class="bi bi-exclamation-triangle text-danger fs-2"></i><br>Caméra refusée';
        });

    document.getElementById('btn-scan')?.addEventListener('click', scanner);

    function scanner() {
        if (!video.videoWidth) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        fetch("{{ route('biometrie.verifier', $cours) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ image: canvas.toDataURL('image/jpeg') })
        })
        .then(r => r.json())
        .then(data => {
            alerteDiv.classList.add('d-none');

            if (data.status === 'success' && !pointes.has(data.etudiant.id)) {
                // ✅ Étudiant reconnu et inscrit dans la classe
                pointes.add(data.etudiant.id);
                sonSucces();

                if (emptyRow) emptyRow.style.display = 'none';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:36px;height:36px;font-size:.8rem;">
                        ${data.etudiant.prenom[0]}${data.etudiant.nom[0]}
                    </div></td>
                    <td><strong>${data.etudiant.prenom} ${data.etudiant.nom}</strong><br>
                        <small class="text-muted">${data.etudiant.matricule}</small></td>
                    <td><span class="badge bg-secondary">${data.heure_pointage}</span></td>
                    <td><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Présent</span></td>
                `;
                presenceList.prepend(tr);

                const total = pointes.size;
                counter.textContent = `${total} Présent${total > 1 ? 's' : ''}`;

            } else if (data.status === 'intruder') {
                // ⚠️ Étudiant pas dans cette classe
                sonInconnu();
                nomInconnu.textContent = `⚠️ ${data.etudiant?.prenom ?? ''} ${data.etudiant?.nom ?? ''} — ÉTUDIANT NON INSCRIT DANS CETTE CLASSE !`;
                alerteDiv.classList.remove('d-none');
                alerteDiv.classList.add('shake');
                setTimeout(() => alerteDiv.classList.remove('shake'), 600);
                setTimeout(() => alerteDiv.classList.add('d-none'), 5000);
            }
        })
        .catch(e => console.error('Erreur scan :', e));
    }
});
</script>
@endsection