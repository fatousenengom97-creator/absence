@extends('layouts.app')

@section('content')
<div class="container-fluid px-4" style="padding-left: 15px;">
    <div class="d-flex justify-content-between align-items-center my-4 pt-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestion des Matières</h1>
            <p class="text-muted small d-none d-md-block">Sélectionnez les critères pour afficher et gérer vos matières sans chevauchement.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-circle me-2"></i> Ajouter une matière
        </button>
    </div>

    <div class="card shadow mb-4 border-0 bg-light">
        <div class="card-body p-3">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">1. Département</label>
                    <select id="filter-dept" class="form-select form-select-sm shadow-sm" onchange="updateFilieres()">
                        <option value="TIC">Département TIC</option>
                        <option value="MPC">Département Maths, Physique, Chimie</option>
                    </select>
                </div>

                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">2. Filière / Branche</label>
                    <select id="filter-filiere" class="form-select form-select-sm shadow-sm" onchange="filterTable()">
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

                <div class="col">
                    <label class="form-label small fw-bold text-secondary mb-1">4. Semestre</label>
                    <select id="filter-semestre" class="form-select form-select-sm shadow-sm" onchange="filterTable()">
                        <option value="S1">Semestre 1 (S1)</option>
                        <option value="S2">Semestre 2 (S2)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 font-weight-bold text-primary d-inline">
                <i class="bi bi-list-stars me-2"></i> Matières correspondantes
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0" id="matieres-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%; padding-left: 20px;">Code</th>
                            <th style="width: 50%;">Nom de la matière</th>
                            <th class="text-center" style="width: 15%;">Coefficient</th>
                            <th class="text-center" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="matieres-tbody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="matiereModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Ajouter une matière</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="matiereForm" onsubmit="saveMatiere(event)">
                <div class="modal-body">
                    <input type="hidden" id="matiere-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Code de la matière</label>
                        <input type="text" id="form-code" class="form-control" placeholder="Ex: WEB2411" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nom de la matière</label>
                        <input type="text" id="form-nom" class="form-control" placeholder="Ex: Programmation PHP" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Coefficient</label>
                        <input type="number" id="form-coef" class="form-control" value="1" min="1" required>
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
    // Liste globale simulée contenant la logique spécifique de l'UFR
    let matieresSimulees = [
        { id: 1, dept: 'TIC', filiere: 'D2A', niveau: 'L1', semestre: 'S1', code: 'WEB1111', nom: 'Architecture des ordinateurs', coef: 1 },
        { id: 2, dept: 'TIC', filiere: 'D2A', niveau: 'L1', semestre: 'S1', code: 'WEB1112', nom: 'Système d\'exploitation', coef: 1 },
        { id: 3, dept: 'TIC', filiere: 'D2A', niveau: 'L1', semestre: 'S2', code: 'WEB1211', nom: 'HTML et CSS', coef: 2 },
        { id: 4, dept: 'TIC', filiere: 'SRT', niveau: 'L1', semestre: 'S1', code: 'SRT1110', nom: 'Introduction aux Réseaux', coef: 2 },
        
        // Département MPC - L1 Tronc commun
        { id: 5, dept: 'MPC', filiere: 'MPCI', niveau: 'L1', semestre: 'S1', code: 'MTH1110', nom: 'Algèbre 1', coef: 3 },
        
        // Département MPC - L2 Branches de spécialisation
        { id: 6, dept: 'MPC', filiere: 'MPI', niveau: 'L2', semestre: 'S3', code: 'INF2310', nom: 'Structure de Données (MPI)', coef: 2 },
        { id: 7, dept: 'MPC', filiere: 'PC', niveau: 'L2', semestre: 'S3', code: 'CHM2310', nom: 'Chimie Organique (PC)', coef: 3 },
        { id: 8, dept: 'MPC', filiere: 'SID', niveau: 'L2', semestre: 'S3', code: 'STA2310', nom: 'Probabilités et Statistiques (SID)', coef: 3 }
    ];

    let bootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        bootstrapModal = new bootstrap.Modal(document.getElementById('matiereModal'));
        updateFilieres(); // Initialisation des filières au premier chargement
    });

    // Ajustement dynamique des filières et des semestres selon le niveau
    function updateFilieres() {
        const dept = document.getElementById('filter-dept').value;
        const niveau = document.getElementById('filter-niveau').value;
        const filiereSelect = document.getElementById('filter-filiere');
        const semestreSelect = document.getElementById('filter-semestre');

        // 1. Changement des choix de semestres selon le niveau
        if (niveau === 'L1') {
            semestreSelect.innerHTML = '<option value="S1">Semestre 1 (S1)</option><option value="S2">Semestre 2 (S2)</option>';
        } else if (niveau === 'L2') {
            semestreSelect.innerHTML = '<option value="S3">Semestre 3 (S3)</option><option value="S4">Semestre 4 (S4)</option>';
        } else {
            semestreSelect.innerHTML = '<option value="S5">Semestre 5 (S5)</option><option value="S6">Semestre 6 (S6)</option>';
        }

        // 2. Gestion de la logique des filières (Spécialisation MPC en L2/L3)
        if (dept === 'TIC') {
            filiereSelect.innerHTML = '<option value="D2A">D2A</option><option value="SRT">SRT</option>';
        } else if (dept === 'MPC') {
            if (niveau === 'L1') {
                filiereSelect.innerHTML = '<option value="MPCI">MPCI (Tronc Commun)</option>';
            } else {
                filiereSelect.innerHTML = '<option value="MPI">MPI</option><option value="PC">PC</option><option value="SID">SID</option>';
            }
        }
        filterTable();
    }

    function updateFiliereBranches() {
        updateFilieres();
    }

    // Filtrage dynamique du tableau
    function filterTable() {
        const dept = document.getElementById('filter-dept').value;
        const filiere = document.getElementById('filter-filiere').value;
        const niveau = document.getElementById('filter-niveau').value;
        const semestre = document.getElementById('filter-semestre').value;

        const tbody = document.getElementById('matieres-tbody');
        tbody.innerHTML = '';

        const resultats = matieresSimulees.filter(m => m.dept === dept && m.filiere === filiere && m.niveau === niveau && m.semestre === semestre);

        if (resultats.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-exclamation-circle me-2"></i>Aucune matière configurée pour cette sélection.</td></tr>`;
            return;
        }

        resultats.forEach(m => {
            tbody.innerHTML += `
                <tr>
                    <td style="padding-left: 20px;"><span class="badge bg-secondary px-2 py-2">${m.code}</span></td>
                    <td class="fw-bold text-dark">${m.nom}</td>
                    <td class="text-center fw-bold">${m.coef}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-warning me-2" onclick="openEditModal(${m.id})">
                            <i class="bi bi-pencil"></i> Modifier
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMatiere(${m.id})">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // Opérations d'interface du CRUD (Ajout/Modification/Suppression)
    function openAddModal() {
        document.getElementById('modal-title').innerText = "Ajouter une matière";
        document.getElementById('matiere-id').value = '';
        document.getElementById('form-code').value = '';
        document.getElementById('form-nom').value = '';
        document.getElementById('form-coef').value = '1';
        bootstrapModal.show();
    }

    function openEditModal(id) {
        const mat = matieresSimulees.find(m => m.id === id);
        if(mat) {
            document.getElementById('modal-title').innerText = "Modifier la matière";
            document.getElementById('matiere-id').value = mat.id;
            document.getElementById('form-code').value = mat.code;
            document.getElementById('form-nom').value = mat.nom;
            document.getElementById('form-coef').value = mat.coef;
            bootstrapModal.show();
        }
    }

    function saveMatiere(e) {
        e.preventDefault();
        const id = document.getElementById('matiere-id').value;
        const code = document.getElementById('form-code').value;
        const nom = document.getElementById('form-nom').value;
        const coef = document.getElementById('form-coef').value;

        if (id) {
            let mat = matieresSimulees.find(m => m.id == id);
            if(mat) {
                mat.code = code;
                mat.nom = nom;
                mat.coef = coef;
            }
        } else {
            const newMat = {
                id: Date.now(),
                dept: document.getElementById('filter-dept').value,
                filiere: document.getElementById('filter-filiere').value,
                niveau: document.getElementById('filter-niveau').value,
                semestre: document.getElementById('filter-semestre').value,
                code: code,
                nom: nom,
                coef: coef
            };
            matieresSimulees.push(newMat);
        }

        bootstrapModal.hide();
        filterTable();
    }

    function deleteMatiere(id) {
        if(confirm("Voulez-vous vraiment supprimer cette matière ?")) {
            matieresSimulees = matieresSimulees.filter(m => m.id !== id);
            filterTable();
        }
    }
</script>
@endsection