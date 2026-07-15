@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Section Vidéo -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <div class="d-flex align-items-center">
                        <div class="spinner-grow spinner-grow-sm text-danger me-2" role="status" id="ping-icon"></div>
                        <h5 class="mb-0 fw-bold">Pointage Biométrique — {{ $cours->matiere->nomMatiere }}</h5>
                    </div>
                    <span id="status-badge" class="badge bg-warning text-dark px-3 py-2 fw-semibold">INITIALISATION DES CAPTEURS...</span>
                </div>
                <!-- Retour Caméra & Canvas -->
                <div class="card-body position-relative text-center bg-dark d-flex align-items-center justify-content-center" style="min-height: 500px; padding: 10px;">
                    <video id="webcam" autoplay muted width="640" height="480" style="border-radius: 8px; max-width: 100%; height: auto; border: 2px solid #334155;"></video>
                    <!-- Superposition parfaite du Canvas -->
                    <canvas id="overlay" style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); pointer-events: none;"></canvas>
                </div>
                <!-- Indicateurs d'état en bas de la vidéo -->
                <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Positionnez-vous face à la caméra à environ 1 mètre.</small>
                    <button id="toggle-audio" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-volume-up-fill" id="audio-icon"></i> Vocal : Actif
                    </button>
                </div>
            </div>
        </div>

        <!-- Section Liste des présents -->
        <div class="col-md-4">
            <div class="card shadow-lg h-100 border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Présents au cours</h5>
                    <span id="compteur-presents" class="badge bg-white text-primary rounded-pill fs-6 px-3">0</span>
                </div>
                <div class="card-body" style="max-height: 460px; overflow-y: auto; background-color: #f8fafc;">
                    <ul id="liste-presents" class="list-group list-group-flush" style="border-radius: 8px;">
                        <li class="text-center text-muted py-5" id="placeholder-liste">
                            <i class="bi bi-person-bounding-box d-block fs-1 text-secondary mb-3 animate__pulse"></i>
                            <span>En attente de la première détection...</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclusion des librairies CSS d'animation pour une UI moderne -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Scripts face-api.js -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('overlay');
        const statusBadge = document.getElementById('status-badge');
        const toggleAudioBtn = document.getElementById('toggle-audio');
        const audioIcon = document.getElementById('audio-icon');
        const pingIcon = document.getElementById('ping-icon');
        
        const coursId = "{{ $cours->id }}";
        const etudiantsEnregistres = new Set(); 
        let audioFeedbackEnabled = true;

        // Configuration du commutateur audio pour l'enseignant
        toggleAudioBtn.addEventListener('click', () => {
            audioFeedbackEnabled = !audioFeedbackEnabled;
            if (audioFeedbackEnabled) {
                toggleAudioBtn.className = "btn btn-sm btn-outline-secondary";
                toggleAudioBtn.innerHTML = '<i class="bi bi-volume-up-fill me-1"></i> Vocal : Actif';
            } else {
                toggleAudioBtn.className = "btn btn-sm btn-outline-danger";
                toggleAudioBtn.innerHTML = '<i class="bi bi-volume-mute-fill me-1"></i> Vocal : Désactivé';
            }
        });

        // 1. Récupération des données d'étudiants injectées par Laravel
        const labelsEtudiants = @json($etudiantsData); 

        // 2. Création d'un dictionnaire rapide [id] -> [nom]
        const dictionnaireNoms = {};
        labelsEtudiants.forEach(etudiant => {
            dictionnaireNoms[etudiant.id.toString()] = etudiant.nom;
        });

        // 3. Démarrage du flux Webcam
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
            video.srcObject = stream;
        } catch (err) {
            console.error("Erreur d'accès à la webcam : ", err);
            statusBadge.className = "badge bg-danger text-white px-3 py-2";
            statusBadge.textContent = "🔴 ERREUR DE CAPTEUR";
            pingIcon.className = "d-none";
            alert("Erreur matérielle : Impossible d'activer le capteur vidéo.");
            return;
        }

        // 4. Chargement des algorithmes d'IA (Réseaux de neurones convolutifs)
        try {
            const modelPath = '/models'; 
            await faceapi.nets.tinyFaceDetector.loadFromUri(modelPath);
            await faceapi.nets.faceLandmark68Net.loadFromUri(modelPath);
            await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);
            await faceapi.nets.ssdMobilenetv1.loadFromUri(modelPath);
            
            statusBadge.className = "badge bg-info text-white px-3 py-2";
            statusBadge.textContent = "CHARGEMENT DE LA BASE BIOMÉTRIQUE...";
        } catch (err) {
            console.error("Erreur de chargement des modèles :", err);
            statusBadge.textContent = "🔴 ERREUR ALGORITHMES";
            return;
        }

        // 5. Instanciation du comparateur de visages (Face Matcher)
        let faceMatcher = null;
        try {
            faceMatcher = await chargerPhotosReference(labelsEtudiants);
            statusBadge.className = "badge bg-success text-white px-3 py-2 animate__animated animate__pulse animate__infinite";
            statusBadge.textContent = "🔴 RECONNAISSANCE FACIALE ACTIVE";
            pingIcon.className = "spinner-grow spinner-grow-sm text-success me-2";
        } catch (err) {
            console.error("Impossible d'initialiser le FaceMatcher :", err);
            statusBadge.className = "badge bg-danger text-white px-3 py-2";
            statusBadge.textContent = "🔴 ERREUR CORRESPONDANCE";
            return;
        }

        // 6. Calibrage des dimensions physiques du Canvas de traçage
        const displaySize = { width: 640, height: 480 };
        faceapi.matchDimensions(canvas, displaySize);

        // 7. Lancement de l'analyse séquentielle
        demarrerDetection();

        function demarrerDetection() {
            const ctx = canvas.getContext('2d');
            
            setInterval(async () => {
                if (video.paused || video.ended) return;

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                
                // Nettoyage complet du canvas à chaque frame
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                resizedDetections.forEach(detection => {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    const label = bestMatch.label;
                    const box = detection.detection.box;

                    // Traitement dynamique de l'étudiant identifié
                    if (label !== 'unknown') {
                        if (!etudiantsEnregistres.has(label)) {
                            etudiantsEnregistres.add(label); 
                            validerPresenceServeur(label);
                        }
                    }

                    // Calcul de la précision de concordance
                    const nomAffichage = label !== 'unknown' ? (dictionnaireNoms[label] || `Étudiant #${label}`) : 'Inconnu';
                    const tauxPrecision = Math.round((1 - bestMatch.distance) * 100);

                    // --- TRACING FUTURISTE PERSONNALISÉ (Canvas 2D) ---
                    const { x, y, width, height } = box;
                    const color = label !== 'unknown' ? '#10b981' : '#ef4444'; // Vert si connu, Rouge si inconnu

                    // 1. Dessiner le cadre de détection stylisé (coins renforcés)
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, y, width, height);

                    // Coins épaissis style "Cible de tracking"
                    ctx.fillStyle = color;
                    const cornerLen = 20;
                    const thick = 5;
                    // Haut Gauche
                    ctx.fillRect(x - 2, y - 2, cornerLen, thick);
                    ctx.fillRect(x - 2, y - 2, thick, cornerLen);
                    // Haut Droite
                    ctx.fillRect(x + width - cornerLen + 2, y - 2, cornerLen, thick);
                    ctx.fillRect(x + width - thick + 2, y - 2, thick, cornerLen);
                    // Bas Gauche
                    ctx.fillRect(x - 2, y + height - thick + 2, cornerLen, thick);
                    ctx.fillRect(x - 2, y + height - cornerLen + 2, thick, cornerLen);
                    // Bas Droite
                    ctx.fillRect(x + width - cornerLen + 2, y + height - thick + 2, cornerLen, thick);
                    ctx.fillRect(x + width - thick + 2, y + height - cornerLen + 2, thick, cornerLen);

                    // 2. Dessiner l'étiquette nominative
                    ctx.font = "bold 13px Arial";
                    const labelText = `${nomAffichage} (${tauxPrecision}%)`;
                    const textWidth = ctx.measureText(labelText).width;
                    ctx.fillStyle = color;
                    ctx.fillRect(x - 2, y - 25, textWidth + 14, 23);

                    ctx.fillStyle = "#ffffff";
                    ctx.fillText(labelText, x + 5, y - 9);
                });
            }, 1000); 
        }

        // Synthèse vocale de validation de présence (Feedback sonore pour le jury)
        function emettreFeedbackVocal(nomEtudiant) {
            if ('speechSynthesis' in window && audioFeedbackEnabled) {
                window.speechSynthesis.cancel(); // Annule une phrase en cours de lecture
                const utterance = new SpeechSynthesisUtterance(`Présence validée pour ${nomEtudiant}`);
                utterance.lang = 'fr-FR';
                utterance.rate = 1.0; 
                utterance.pitch = 1.1;
                window.speechSynthesis.speak(utterance);
            }
        }

        // Chargement unifié et tolérant des images de référence
        async function chargerPhotosReference(etudiants) {
            const descriptions = [];

            for (const etudiant of etudiants) {
                try {
                    const img = await faceapi.fetchImage(etudiant.photo_url);
                    const detection = await faceapi.detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        descriptions.push(new faceapi.LabeledFaceDescriptors(
                            etudiant.id.toString(), 
                            [detection.descriptor]
                        ));
                    } else {
                        console.warn(`Pas de signature faciale générable pour : ${etudiant.nom}`);
                    }
                } catch (e) {
                    console.error(`Impossible d'atteindre le fichier média de ${etudiant.nom} :`, e);
                }
            }

            if (descriptions.length === 0) {
                throw new Error("Base de signatures biométriques vide.");
            }

            // Seuil de tolérance (distance euclidienne) ajusté à 0.55 pour limiter les faux positifs
            return new faceapi.FaceMatcher(descriptions, 0.55); 
        }

        // Transmission asynchrone sécurisée à l'API Laravel
        function validerPresenceServeur(etudiantId) {
            fetch("{{ route('api.pointage.valider') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    etudiant_id: etudiantId,
                    cours_id: coursId
                })
            })
            .then(res => {
                if (!res.ok) throw new Error("Réponse serveur défectueuse");
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    ajouterALaListeDePresence(data.message);
                    
                    // Lancer la confirmation vocale
                    const nomEtudiant = dictionnaireNoms[etudiantId] || "Étudiant";
                    emettreFeedbackVocal(nomEtudiant);
                } else {
                    etudiantsEnregistres.delete(etudiantId);
                }
            })
            .catch(err => {
                console.error("Échec de synchronisation avec le serveur :", err);
                etudiantsEnregistres.delete(etudiantId);
            });
        }

        function ajouterALaListeDePresence(message) {
            const placeholder = document.getElementById('placeholder-liste');
            if (placeholder) placeholder.remove();

            const liste = document.getElementById('liste-presents');
            const totalBadge = document.getElementById('compteur-presents');
            
            if (![...liste.children].some(li => li.textContent.includes(message))) {
                const li = document.createElement('li');
                li.className = "list-group-item d-flex justify-content-between align-items-center mb-2 rounded border-0 shadow-sm animate__animated animate__fadeInDown";
                li.style.backgroundColor = "#e2f1e9"; // Teinte de vert douce
                li.style.borderLeft = "4px solid #10b981";
                li.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-patch-check-fill text-success fs-5 me-2"></i>
                        <span class="fw-semibold text-dark">${message}</span>
                    </div>
                    <small class="text-muted fw-light">À l'instant</small>
                `;
                liste.prepend(li);

                const nbPresents = liste.querySelectorAll('li:not(#placeholder-liste)').length;
                totalBadge.textContent = nbPresents;
            }
        }
    });
</script>
@endsection