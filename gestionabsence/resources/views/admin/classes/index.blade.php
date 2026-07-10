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
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="classe-id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nom complet de la classe</label>
                        <input type="text" id="form-nom" name="nom" class="form-control" placeholder="Ex: D2A Licence 1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Année Scolaire</label>
                        <input type="text" id="form-annee" name="annee" class="form-control" value="2025-2026" placeholder="Ex: 2025-2026" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Effectif Initial (Étudiants)</label>
                        <input type="number" id="form-effectif" name="effectif" class="form-control" value="0" min="0" required>
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
    let classeBootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        classeBootstrapModal = new bootstrap.Modal(document.getElementById('classeModal'));
        updateFilieres(); 
    });

    // Gère l'affichage des filières dans le select secondaire selon la logique de l'UFR
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

    // RÉCUPÉRATION DYNAMIQUE DEPUIS LA BASE DE DONNÉES (FETCH)
    async function filterClassesTable() {
        const dept = document.getElementById('filter-dept').value;
        const filiere = document.getElementById('filter-filiere').value;
        const niveau = document.getElementById('filter-niveau').value;
        const tbody = document.getElementById('classes-tbody');
        
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Chargement des classes...</td></tr>`;

        try {
            // Appel AJAX à l'API Laravel en lui transmettant les critères choisis
            const response = await fetch(`/admin/classes/filter?dept=${dept}&filiere=${filiere}&niveau=${niveau}`);
            const classes = await response.json();

            tbody.innerHTML = '';

            if (classes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-exclamation-circle me-2"></i>Aucune classe réelle enregistrée en base pour cette sélection.</td></tr>`;
                return;
            }

            classes.forEach(c => {
                // Utilise les clés correspondantes à ton modèle de base de données (idClasse ou id, nomClasse ou nom)
                const id = c.idClasse ?? c.id;
                const nom = c.nomClasse ?? c.nom;
                const annee = c.anneeScolaire ?? c.annee ?? '2025-2026';
                const effectif = c.effectif ?? 0;

                tbody.innerHTML += `
                    <tr>
                        <td style="padding-left: 20px;" class="fw-bold text-dark">${nom}</td>
                        <td>${annee}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-3 py-2 fw-bold">${effectif} étudiant(s)</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning me-2" onclick="openEditClassModal(${id})">
                                <i class="bi bi-pencil"></i> Modifier
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteClasse(${id})">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error("Erreur de filtrage :", error);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4"><i class="bi bi-x-circle me-2"></i>Erreur lors de la récupération des données.</td></tr>`;
        }
    }

    function openAddClassModal() {
        document.getElementById('modal-title').innerText = "Ajouter une classe";
        document.getElementById('classe-id').value = '';
        document.getElementById('classeForm').reset();
        document.getElementById('form-annee').value = '2025-2026';
        document.getElementById('form-effectif').value = '0';
        classeBootstrapModal.show();
    }

    // PRÉ-REMPLISSAGE DYNAMIQUE AVANT MODIFICATION via un Fetch ciblé
    async function openEditClassModal(id) {
        try {
            const response = await fetch(`/admin/classes/${id}/edit`);
            const classe = await response.json();
            
            if(classe) {
                document.getElementById('modal-title').innerText = "Modifier la classe";
                document.getElementById('classe-id').value = classe.idClasse ?? classe.id;
                document.getElementById('form-nom').value = classe.nomClasse ?? classe.nom;
                document.getElementById('form-annee').value = classe.anneeScolaire ?? classe.annee;
                document.getElementById('form-effectif').value = classe.effectif;
                classeBootstrapModal.show();
            }
        } catch (error) {
            alert("Impossible de charger les détails de la classe.");
        }
    }

    // SAUVEGARDE ET PERSISTANCE RÉELLE (STORE & UPDATE)
    async function saveClasse(e) {
        e.preventDefault();
        
        const form = document.getElementById('classeForm');
        const formData = new FormData(form);
        const id = document.getElementById('classe-id').value;
        
        // Ajout des critères structurels pour que le contrôleur sache où classer l'entité
        formData.append('dept', document.getElementById('filter-dept').value);
        formData.append('filiere', document.getElementById('filter-filiere').value);
        formData.append('niveau', document.getElementById('filter-niveau').value);

        const url = id ? `/admin/classes/${id}/update` : '/admin/classes/store';

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (response.ok && result.success) {
                alert('Classe enregistrée avec succès en base de données !');
                classeBootstrapModal.hide();
                filterClassesTable(); // Rafraîchit instantanément le tableau
            } else {
                alert("Erreur lors de l'enregistrement : " + JSON.stringify(result.errors));
            }
        } catch (error) {
            console.error("Erreur système :", error);
            alert("Une erreur est survenue lors de la communication avec le serveur.");
        }
    }

    // SUPPRESSION RÉELLE EN BASE DE DONNÉES
    async function deleteClasse(id) {
        if(confirm("Voulez-vous vraiment supprimer définitivement cette classe de la base de données ?")) {
            try {
                const response = await fetch(`/admin/classes/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();
                
                if (response.ok && result.success) {
                    alert('Classe supprimée avec succès.');
                    filterClassesTable(); // Rafraîchit instantanément le tableau sans recharger
                } else {
                    alert('Erreur lors de la suppression.');
                }
            } catch (error) {
                console.error("Erreur suppression :", error);
            }
        }
    }
</script>
@endsection