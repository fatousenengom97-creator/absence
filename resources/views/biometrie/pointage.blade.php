@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Colonne Gauche : Flux Vidéo de la Caméra -->
        <div class="col-md-6 text-center">
            <div class="card shadow border-0 bg-dark text-white p-3 mb-4">
                <h4 class="card-title text-success">
                    <i class="fas fa-camera"></i> Scanner Facial en Temps Réel
                </h4>
                <p class="text-muted small">Le système analyse le flux vidéo toutes les 3 secondes</p>
                
                <div class="position-relative d-inline-block mx-auto border border-success rounded overflow-hidden" style="max-width: 500px; width: 100%;">
                    <!-- Balise Vidéo pour la Webcam -->
                    <video id="video" width="100%" height="auto" autoplay muted playsinline class="bg-black"></video>
                    
                    <!-- Canevas invisible servant à capturer la frame -->
                    <canvas id="canvas" width="640" height="480" class="d-none"></canvas>
                </div>

                <div id="status-scanner" class="mt-3 text-warning">
                    <span class="spinner-border spinner-border-sm me-2"></span> Recherche de visages...
                </div>
            </div>
        </div>

        <!-- Colonne Droite : Infos du Cours & Liste des Étudiants -->
        <div class="col-md-6">
            <div class="card shadow border-0 p-4 mb-4">
                <h3 class="text-primary mb-1">{{ $cours->matiere->nomMatiere }}</h3>
                <p class="text-muted">
                    <strong>Classe :</strong> {{ $cours->classe->nom }} | 
                    <strong>Salle :</strong> {{ $cours->salle->nom }}
                </p>
                <hr>

                <h5 class="mb-3">Liste de la classe ({{ $etudiants->count() }} étudiants)</h5>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle" id="table-etudiants">
                        <thead class="table-light">
                            <tr>
                                <th>Matricule</th>
                                <th>Nom Complet</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($etudiants as $etudiant)
                                <tr id="etudiant-row-{{ $etudiant->id }}">
                                    <td><code>{{ $etudiant->codePar }}</code></td>
                                    <td>{{ $etudiant->user->prenom }} {{ $etudiant->user->nom }}</td>
                                    <td>
                                        <span class="badge bg-danger status-badge" id="badge-{{ $etudiant->id }}">Absent</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <form action="{{ route('cours.terminer', $cours->idCours) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100 py-2">
                            <i class="fas fa-stop-circle"></i> Clôturer et Terminer le Cours
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script JavaScript pour piloter la Webcam et le traitement AJAX -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const statusScanner = document.getElementById('status-scanner');

        // 1. Initialiser la caméra de l'appareil
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                    // Lancer la boucle de vérification automatique toutes les 3 secondes
                    setInterval(capturerEtVerifier, 3000);
                })
                .catch(function(error) {
                    console.error("Erreur d'accès à la caméra : ", error);
                    statusScanner.className = "mt-3 text-danger";
                    statusScanner.innerHTML = "<i class='fas fa-exclamation-triangle'></i> Impossible d'accéder à la webcam.";
                });
        }

        // 2. Prendre une photo instantanée et l'envoyer au contrôleur Laravel
        function capturerEtVerifier() {
            if (video.paused || video.ended) return;

            // Dessiner l'image courante de la vidéo sur le canvas invisible
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Convertir le dessin en chaîne Base64 (JPEG)
            const imageBase64 = canvas.toDataURL('image/jpeg');

            statusScanner.className = "mt-3 text-info";
            statusScanner.innerHTML = "<span class='spinner-border spinner-border-sm me-2'></span> Analyse en cours...";

            // Envoi de la requête AJAX vers la méthode verifierVisage()
            fetch("{{ route('biometrie.verifier', $cours->idCours) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ image: imageBase64 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    statusScanner.className = "mt-3 text-success";
                    statusScanner.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message} (${data.confiance}%)`;
                    
                    // Mettre à jour visuellement la ligne de l'étudiant dans le tableau
                    const badge = document.getElementById(`badge-${data.etudiant.id}`);
                    if (badge) {
                        badge.className = "badge bg-success";
                        badge.innerText = `Présent (${data.heure_pointage})`;
                    }
                } else if (data.status === 'intruder') {
                    statusScanner.className = "mt-3 text-warning";
                    statusScanner.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message} ${data.etudiant ? '(' + data.etudiant.prenom + ' ' + data.etudiant.nom + ')' : ''}`;
                } else {
                    statusScanner.className = "mt-3 text-muted";
                    statusScanner.innerHTML = `<i class="fas fa-user-slash"></i> Aucun visage correspondant détecté.`;
                }
            })
            .catch(error => {
                console.error("Erreur d'analyse :", error);
                statusScanner.className = "mt-3 text-danger";
                statusScanner.innerHTML = "<i class='fas fa-wifi'></i> Erreur de communication avec le serveur.";
            });
        }
    });
</script>
@endsection