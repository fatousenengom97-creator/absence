@extends('layouts.app')

@section('content')
<div class="container-fluid px-4" style="padding-left: 15px;">
    <div class="d-flex justify-content-between align-items-center my-4 pt-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestion des Classes</h1>
            <p class="text-muted small d-none d-md-block">Sélectionnez les critères pour afficher, modifier ou ajouter une classe.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAddClassModal()">
            <i class="bi bi-plus-circle me-2"></i> Ajouter une classe
        </button>
    </div>

    <div class="card shadow mb-4 border-0 bg-light">
        <div class="card-body p-3">
            <div class="row row-cols-1 row-cols-md-3 g-3">
                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">1. Département</label>
                    <select id="filter-dept" class="form-select form-select-sm shadow-sm" onchange="updateFilieres()">
                        <option value="TIC">Département TIC</option>
                        <option value="MPC">Département Maths, Physique, Chimie</option>
                    </select>
                </div>

                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">2. Filière / Branche</label>
                    <select id="filter-filiere" class="form-select form-select-sm shadow-sm" onchange="filterClassesTable()">
                        </select>
                </div>

                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">3. Niveau</label>
                    <select id="filter-niveau" class="form-select form-select-sm shadow-sm" onchange="updateFiliereBranches()">
                        <option value="L1">Licence 1 (L1)</option>
                        <option value="L2">Licence 2 (L2)</option>
                        <option value="L3">Licence 3 (L3)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 font-weight-bold text-primary d-inline">
                <i class="bi bi-person-workspace me-2"></i> Classes correspondantes
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0" id="classes-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 35%; padding-left: 20px;">Nom de la classe</th>
                            <th style="width: 25%;">Année Scolaire</th>
                            <th class="text-center" style="width: 20%;">Effectif</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="classes-tbody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="classeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Ajouter une classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="classeForm" onsubmit="saveClasse(event)">
                <div class="modal-body">
                    <input type="hidden" id="classe-id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nom complet de la classe</label>
                        <input type="text" id="form-nom" class="form-control" placeholder="Ex: D2A Licence 1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Année Scolaire</label>
                        <input type="text" id="form-annee" class="form-control" value="2025-2026" placeholder="Ex: 2025-2026" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Effectif Initial (Étudiants)</label>
                        <input type="number" id="form-effectif" class="form-control" value="0" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Données simulées basées sur les filières officielles de l'UFR
    let classesSimulees = [
        // Département TIC - D2A & SRT
        { id: 1, dept: 'TIC', filiere: 'D2A', niveau: 'L1', nom: 'D2A Licence 1', annee: '2025-2026', effectif: 14 },
        { id: 2, dept: 'TIC', filiere: 'D2A', niveau: 'L2', nom: 'D2A Licence 2', annee: '2025-2026', effectif: 14 },
        { id: 3, dept: 'TIC', filiere: 'D2A', niveau: 'L3', nom: 'D2A Licence 3', annee: '2025-2026', effectif: 10 },
        { id: 4, dept: 'TIC', filiere: 'SRT', niveau: 'L1', nom: 'SRT Licence 1', annee: '2025-2026', effectif: 14 },
        { id: 5, dept: 'TIC', filiere: 'SRT', niveau: 'L2', nom: 'SRT Licence 2', annee: '2025-2026', effectif: 11 },
        
        // Département MPC - L1 Tronc commun MPCI
        { id: 6, dept: 'MPC', filiere: 'MPCI', niveau: 'L1', nom: 'MPCI Licence 1', annee: '2025-2026', effectif: 25 },
        
        // Département MPC - L2/L3 Branches (MPI, PC, SID)
        { id: 7, dept: 'MPC', filiere: 'MPI', niveau: 'L2', nom: 'MPI Licence 2', annee: '2025-2026', effectif: 12 },
        { id: 8, dept: 'MPC', filiere: 'PC', niveau: 'L2', nom: 'PC Licence 2', annee: '2025-2026', effectif: 8 },
        { id: 9, dept: 'MPC', filiere: 'SID', niveau: 'L2', nom: 'SID Licence 2', annee: '2025-2026', effectif: 15 }
    ];

    let classeBootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        classeBootstrapModal = new bootstrap.Modal(document.getElementById('classeModal'));
        updateFilieres(); 
    });

    // Gestion propre des filières selon le département et le niveau
    function updateFilieres() {
        const dept = document.getElementById('filter-dept').value;
        const niveau = document.getElementById('filter-niveau').value;
        const filiereSelect = document.getElementById('filter-filiere');

        if (dept === 'TIC') {
            filiereSelect.innerHTML = `
                <option value="D2A">D2A</option>
                <option value="SRT">SRT</option>
            `;
        } else if (dept === 'MPC') {
            if (niveau === 'L1') {
                filiereSelect.innerHTML = '<option value="MPCI">MPCI</option>';
            } else {
                filiereSelect.innerHTML = `
                    <option value="MPI">MPI</option>
                    <option value="PC">PC</option>
                    <option value="SID">SID</option>
                `;
            }
        }
        filterClassesTable();
    }

    function updateFiliereBranches() {
        updateFilieres();
    }

    // Filtrage et affichage dans le tableau
    function filterClassesTable() {
        const dept = document.getElementById('filter-dept').value;
        const filiere = document.getElementById('filter-filiere').value;
        const niveau = document.getElementById('filter-niveau').value;

        const tbody = document.getElementById('classes-tbody');
        tbody.innerHTML = '';

        const resultats = classesSimulees.filter(c => c.dept === dept && c.filiere === filiere && c.niveau === niveau);

        if (resultats.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-exclamation-circle me-2"></i>Aucune classe configurée pour cette sélection.</td></tr>`;
            return;
        }

        resultats.forEach(c => {
            tbody.innerHTML += `
                <tr>
                    <td style="padding-left: 20px;" class="fw-bold text-dark">${c.nom}</td>
                    <td>${c.annee}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-3 py-2 fw-bold">${c.effectif} étudiant(s)</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-warning me-2" onclick="openEditClassModal(${c.id})">
                            <i class="bi bi-pencil"></i> Modifier
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteClasse(${c.id})">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // Fonctions CRUD
    function openAddClassModal() {
        document.getElementById('modal-title').innerText = "Ajouter une classe";
        document.getElementById('classe-id').value = '';
        document.getElementById('form-nom').value = '';
        document.getElementById('form-annee').value = '2025-2026';
        document.getElementById('form-effectif').value = '0';
        classeBootstrapModal.show();
    }

    function openEditClassModal(id) {
        const classe = classesSimulees.find(c => c.id === id);
        if(classe) {
            document.getElementById('modal-title').innerText = "Modifier la classe";
            document.getElementById('classe-id').value = classe.id;
            document.getElementById('form-nom').value = classe.nom;
            document.getElementById('form-annee').value = classe.annee;
            document.getElementById('form-effectif').value = classe.effectif;
            classeBootstrapModal.show();
        }
    }

    function saveClasse(e) {
        e.preventDefault();
        const id = document.getElementById('classe-id').value;
        const nom = document.getElementById('form-nom').value;
        const annee = document.getElementById('form-annee').value;
        const effectif = parseInt(document.getElementById('form-effectif').value);

        if (id) {
            let classe = classesSimulees.find(c => c.id == id);
            if(classe) {
                classe.nom = nom;
                classe.annee = annee;
                classe.effectif = effectif;
            }
        } else {
            const newClasse = {
                id: Date.now(),
                dept: document.getElementById('filter-dept').value,
                filiere: document.getElementById('filter-filiere').value,
                niveau: document.getElementById('filter-niveau').value,
                nom: nom,
                annee: annee,
                effectif: effectif
            };
            classesSimulees.push(newClasse);
        }

        classeBootstrapModal.hide();
        filterClassesTable();
    }

    function deleteClasse(id) {
        if(confirm("Voulez-vous vraiment supprimer cette classe ?")) {
            classesSimulees = classesSimulees.filter(c => c.id !== id);
            filterClassesTable();
        }
    }
</script>
@endsection