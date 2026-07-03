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
            <div class="row row-cols-1 row-cols-md-2 g-3">
                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">1. Sélectionner la Classe</label>
                    <select id="filter-classe" class="form-select form-select-sm shadow-sm" onchange="filterCoursTable()">
                        <option value="D2A Licence 1">D2A Licence 1</option>
                        <option value="D2A Licence 2">D2A Licence 2</option>
                        <option value="D2A Licence 3">D2A Licence 3</option>
                        <option value="SRT Licence 1">SRT Licence 1</option>
                        <option value="MPCI Licence 1">MPCI Licence 1</option>
                    </select>
                </div>

                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">2. Semestre</label>
                    <select id="filter-semestre" class="form-select form-select-sm shadow-sm" onchange="filterCoursTable()">
                        <option value="S1">Semestre 1 (S1)</option>
                        <option value="S2">Semestre 2 (S2)</option>
                        <option value="S3">Semestre 3 (S3)</option>
                        <option value="S4">Semestre 4 (S4)</option>
                        <option value="S5">Semestre 5 (S5)</option>
                        <option value="S6">Semestre 6 (S6)</option>
                    </select>
                </div>
            </div>
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
                <div class="modal-body">
                    <input type="hidden" id="cours-id">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Jour</label>
                            <select id="form-jour" class="form-select" required>
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
                            <input type="time" id="form-debut" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Heure Fin</label>
                            <input type="time" id="form-fin" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Matière (Filtre selon la classe sélectionnée)</label>
                            <select id="form-matiere" class="form-select" required>
                                <option value="Architecture et technologie des ordinateurs">Architecture et technologie des ordinateurs (WEB1111)</option>
                                <option value="Système d'exploitation">Système d'exploitation (WEB1112)</option>
                                <option value="HTML et CSS">HTML et CSS (WEB1211)</option>
                                <option value="Algèbre 1">Algèbre 1 (MTH1110)</option>
                                <option value="Structure de Données">Structure de Données (INF2310)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Enseignant</label>
                            <select id="form-prof" class="form-select" required>
                                <option value="M. Diop">M. Diop</option>
                                <option value="Mme. Ndiaye">Mme. Ndiaye</option>
                                <option value="M. Sarr">M. Sarr</option>
                                <option value="M. Diallo">M. Diallo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Salle de cours</label>
                            <select id="form-salle" class="form-select" required>
                                <option value="Salle S1">Salle S1</option>
                                <option value="Salle S2">Salle S2</option>
                                <option value="Amphi A">Amphi A</option>
                                <option value="Labo Info">Labo Info</option>
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
    // Simulation des données de l'emploi du temps
    let coursSimules = [
        { id: 1, classe: 'D2A Licence 1', semestre: 'S1', jour: 'Lundi', horaire: '08:00 - 10:00', matiere: 'Architecture et technologie des ordinateurs', prof: 'M. Diop', salle: 'Salle S1' },
        { id: 2, classe: 'D2A Licence 1', semestre: 'S1', jour: 'Lundi', horaire: '10:15 - 12:15', matiere: 'Système d\'exploitation', prof: 'Mme. Ndiaye', salle: 'Labo Info' },
        { id: 3, classe: 'D2A Licence 1', semestre: 'S2', jour: 'Mardi', horaire: '14:00 - 16:00', matiere: 'HTML et CSS', prof: 'M. Sarr', salle: 'Labo Info' },
        { id: 4, classe: 'MPCI Licence 1', semestre: 'S1', jour: 'Mercredi', horaire: '08:00 - 11:00', matiere: 'Algèbre 1', prof: 'M. Diallo', salle: 'Amphi A' }
    ];

    let coursBootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        coursBootstrapModal = new bootstrap.Modal(document.getElementById('coursModal'));
        filterCoursTable(); // Charge le tableau initial
    });

    // Filtrage dynamique selon la classe et le semestre sélectionnés
    function filterCoursTable() {
        const classeSelectionnee = document.getElementById('filter-classe').value;
        const semestreSelectionne = document.getElementById('filter-semestre').value;

        const tbody = document.getElementById('cours-tbody');
        tbody.innerHTML = '';

        const resultats = coursSimules.filter(c => c.classe === classeSelectionnee && c.semestre === semestreSelectionne);

        if (resultats.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-calendar-x me-2"></i>Aucun cours programmé pour cette classe au cours de ce semestre.</td></tr>`;
            return;
        }

        // Tri rapide par jour pour l'affichage (Lundi, Mardi...)
        const ordreJours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
        resultats.sort((a, b) => ordreJours.indexOf(a.jour) - ordreJours.indexOf(b.jour));

        resultats.forEach(c => {
            tbody.innerHTML += `
                <tr>
                    <td style="padding-left: 20px;" class="fw-bold text-secondary">${c.jour}</td>
                    <td class="text-primary fw-bold"><i class="bi bi-clock me-2"></i>${c.horaire}</td>
                    <td class="fw-bold text-dark">${c.matiere}</td>
                    <td><span class="text-muted"><i class="bi bi-person-badge me-1"></i>${c.prof}</span></td>
                    <td><span class="badge bg-light text-primary border border-primary px-2 py-1 fw-bold">${c.salle}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="openEditCoursModal(${c.id})" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCours(${c.id})" title="Annuler">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // CRUD opérations simulées
    function openAddCoursModal() {
        document.getElementById('modal-title').innerText = "Planifier un nouveau cours";
        document.getElementById('cours-id').value = '';
        document.getElementById('form-debut').value = '08:00';
        document.getElementById('form-fin').value = '10:00';
        coursBootstrapModal.show();
    }

    function openEditCoursModal(id) {
        const cours = coursSimules.find(c => c.id === id);
        if(cours) {
            document.getElementById('modal-title').innerText = "Modifier la planification du cours";
            document.getElementById('cours-id').value = cours.id;
            document.getElementById('form-jour').value = cours.jour;
            
            // Extraction rapide des heures
            const heures = cours.horaire.split(' - ');
            document.getElementById('form-debut').value = heures[0];
            document.getElementById('form-fin').value = heures[1];
            
            document.getElementById('form-matiere').value = cours.matiere;
            document.getElementById('form-prof').value = cours.prof;
            document.getElementById('form-salle').value = cours.salle;
            coursBootstrapModal.show();
        }
    }

    function saveCours(e) {
        e.preventDefault();
        const id = document.getElementById('cours-id').value;
        const jour = document.getElementById('form-jour').value;
        const debut = document.getElementById('form-debut').value;
        const fin = document.getElementById('form-fin').value;
        const matiere = document.getElementById('form-matiere').value;
        const prof = document.getElementById('form-prof').value;
        const salle = document.getElementById('form-salle').value;

        const horaireConstruit = `${debut} - ${fin}`;

        if (id) {
            // Mode modification
            let cours = coursSimules.find(c => c.id == id);
            if(cours) {
                cours.jour = jour;
                cours.horaire = horaireConstruit;
                cours.matiere = matiere;
                cours.prof = prof;
                cours.salle = salle;
            }
        } else {
            // Mode ajout : Récupère automatiquement les données de filtre de la classe courante
            const newCours = {
                id: Date.now(),
                classe: document.getElementById('filter-classe').value,
                semestre: document.getElementById('filter-semestre').value,
                jour: jour,
                horaire: horaireConstruit,
                matiere: matiere,
                prof: prof,
                salle: salle
            };
            coursSimules.push(newCours);
        }

        coursBootstrapModal.hide();
        filterCoursTable();
    }

    function deleteCours(id) {
        if(confirm("Voulez-vous vraiment retirer ce cours de l'emploi du temps ?")) {
            coursSimules = coursSimules.filter(c => c.id !== id);
            filterCoursTable();
        }
    }
</script>
@endsection