@extends('layouts.app')

@section('content')
<div class="container-fluid px-4" style="padding-left: 15px;">
    <div class="d-flex justify-content-between align-items-center my-4 pt-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Planification des Cours</h1>
            <p class="text-muted small d-none d-md-block">Gérez l'emploi du temps en associant les classes, les matières, les professeurs et les salles.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAddCoursModal()">
            <i class="bi bi-calendar-plus me-2"></i> Planifier un cours
        </button>
    </div>

    <div class="card shadow mb-4 border-0 bg-light">
        <div class="card-body p-3">
            <form id="filterForm" method="GET" action="{{ url('/admin/cours') }}">
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <div class="col">
                        <label class="form-label small fw-bold text-secondary mb-1">1. Sélectionner la Classe</label>
                        <select id="filter-classe" name="classe_id" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- Toutes les classes --</option>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->idClasse }}" {{ $classeSelectionnee == $classe->idClasse ? 'selected' : '' }}>
                                    {{ $classe->nomClasse ?? $classe->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label small fw-bold text-secondary mb-1">2. Semestre (Optionnel)</label>
                        <select id="filter-semestre" class="form-select form-select-sm shadow-sm" onchange="appliquerFiltreClient()">
                            <option value="Tous">Tous les semestres</option>
                            <option value="S1">Semestre 1 (S1)</option>
                            <option value="S2">Semestre 2 (S2)</option>
                            <option value="S3">Semestre 3 (S3)</option>
                            <option value="S4">Semestre 4 (S4)</option>
                            <option value="S5">Semestre 5 (S5)</option>
                            <option value="S6">Semestre 6 (S6)</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4 border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 font-weight-bold text-primary d-inline">
                <i class="bi bi-calendar3 me-2"></i> Liste des cours planifiés
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0" id="cours-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%; padding-left: 20px;">Jour</th>
                            <th style="width: 15%;">Créneau Horaire</th>
                            <th style="width: 30%;">Matière</th>
                            <th style="width: 20%;">Enseignant</th>
                            <th style="width: 10%;">Salle</th>
                            <th class="text-center" style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cours-tbody">
                        @forelse($cours as $cour)
                            <tr class="ligne-cours" data-semestre="{{ $cour->matiere->ue->semestre ?? 'S1' }}">
                                <td style="padding-left: 20px;" class="fw-bold text-secondary">{{ $cour->jour }}</td>
                                <td class="text-primary fw-bold">
                                    <i class="bi bi-clock me-2"></i>
                                    {{ date('H:i', strtotime($cour->heureDebut)) }} - {{ date('H:i', strtotime($cour->heureFin)) }}
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $cour->matiere->nomMatiere ?? $cour->matiere->nom ?? 'Matière indéfinie' }}
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="bi bi-person-badge me-1"></i>
                                        {{ $cour->professeur->user->nom ?? $cour->professeur->user->name ?? 'Inconnu' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary px-2 py-1 fw-bold">
                                        {{ $cour->salle->nomSalle ?? $cour->salle->nom ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick="openEditCoursModal({{ $cour->idCours }})" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCours({{ $cour->idCours }})" title="Annuler">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x me-2"></i>Aucun cours réel enregistré en base de données pour cette sélection.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="coursModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Planifier un nouveau cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="coursForm" onsubmit="saveCours(event)">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="cours-id" name="id">
                    <input type="hidden" id="form-classe-id" name="idClasse" value="{{ $classeSelectionnee ?? '' }}"> 

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Jour</label>
                            <select id="form-jour" name="jour" class="form-select" required>
                                <option value="Lundi">Lundi</option>
                                <option value="Mardi">Mardi</option>
                                <option value="Mercredi">Mercredi</option>
                                <option value="Jeudi">Jeudi</option>
                                <option value="Vendredi">Vendredi</option>
                                <option value="Samedi">Samedi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Heure Début</label>
                            <input type="time" id="form-debut" name="heureDebut" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Heure Fin</label>
                            <input type="time" id="form-fin" name="heureFin" class="form-control" required>
                        </div>

                        <div class="col-md-12">
    <label class="form-label fw-bold small">Matière</label>
    <select id="form-matiere" name="idMatiere" class="form-select" required>
        <option value="">-- Choisir une matière --</option>
        @foreach($matieres as $matiere)
            <option value="{{ $matiere->idMatiere ?? $matiere->id }}">{{ $matiere->nomMatiere ?? $matiere->nom }}</option>
        @endforeach
    </select>
</div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Enseignant</label>
                            <select id="form-prof" name="professeur_id" class="form-select" required>
                                <option value="">-- Choisir un enseignant --</option>
                                @foreach($professeurs as $prof)
                                    <option value="{{ $prof->id }}">{{ $prof->user->nom ?? $prof->user->name ?? $prof->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
    <label class="form-label fw-bold small">Salle de cours</label>
    <select id="form-salle" name="salle_id" class="form-select" required>
        <option value="">-- Choisir une salle --</option>
        @foreach($salles as $salle)
            <option value="{{ $salle->idSalle ?? $salle->id }}">{{ $salle->nomSalle ?? $salle->nom }}</option>
        @endforeach
    </select>
</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit">Enregistrer le cours</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let coursBootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        coursBootstrapModal = new bootstrap.Modal(document.getElementById('coursModal'));
        
        const idClasseInitiale = document.getElementById('filter-classe').value;
        if(idClasseInitiale) {
            actualiserListeMatieresModal(idClasseInitiale);
        }
    });

    function appliquerFiltreClient() {
        const semestre = document.getElementById('filter-semestre').value;
        const lignes = document.querySelectorAll('.ligne-cours');
        
        lignes.forEach(ligne => {
            if (semestre === 'Tous' || ligne.getAttribute('data-semestre') === semestre) {
                ligne.style.display = '';
            } else {
                ligne.style.display = 'none';
            }
        });
    }

    function actualiserListeMatieresModal(idClasse) {
        if(!idClasse) return;
        fetch(`/admin/cours/matieres-par-classe/${idClasse}`)
            .then(res => res.json())
            .then(data => {
                const selectMatiere = document.getElementById('form-matiere');
                selectMatiere.innerHTML = '<option value="">-- Choisir une matière --</option>';
                data.forEach(m => {
                    const matiereId = m.idMatiere || m.id;
                    const matiereNom = m.nomMatiere || m.nom;
                    
                    selectMatiere.innerHTML += `<option value="${matiereId}">${matiereNom}</option>`;
                });
            }).catch(err => console.error("Erreur de chargement des matières :", err));
    }

    function openAddCoursModal() {
        const idClasseActive = document.getElementById('filter-classe').value;
        if (!idClasseActive) {
            alert('Veuillez d\'abord choisir une classe pour y affecter un cours.');
            return;
        }
        
        document.getElementById('modal-title').innerText = "Planifier un nouveau cours";
        document.getElementById('cours-id').value = '';
        document.getElementById('form-classe-id').value = idClasseActive;
        document.getElementById('coursForm').reset();
        document.getElementById('form-classe-id').value = idClasseActive;
        
        actualiserListeMatieresModal(idClasseActive);
        coursBootstrapModal.show();
    }

    async function saveCours(event) {
        event.preventDefault();

        const form = document.getElementById('coursForm');
        const formData = new FormData(form);
        const coursId = document.getElementById('cours-id').value;
        const url = coursId ? `/admin/cours/${coursId}/update` : '/admin/cours/store';

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Cours enregistré avec succès !');
                coursBootstrapModal.hide();
                window.location.href = window.location.pathname + "?classe_id=" + document.getElementById('filter-classe').value;
            } else {
                alert('Erreur lors de l\'enregistrement : ' + JSON.stringify(result.errors || result));
            }
        } catch (error) {
            console.error('Erreur système :', error);
            alert('Une erreur est survenue lors de l\'enregistrement.');
        }
    }

    function openEditCoursModal(id) {
        alert('Fonction d\'édition à lier avec un fetch(id) ou tes lignes existantes.');
    }

    function deleteCours(id) {
        if(confirm("Voulez-vous vraiment retirer ce cours de l'emploi du temps ?")) {
            alert('Lien de suppression réelle BDD à appeler pour l\'ID: ' + id);
        }
    }
</script>
@endsection