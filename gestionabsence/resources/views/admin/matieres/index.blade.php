@extends('layouts.app')

@section('content')
<div class="container-fluid px-4" style="padding-left: 15px;">
    <div class="d-flex justify-content-between align-items-center my-4 pt-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestion des Matières</h1>
            <p class="text-muted small d-none d-md-block">Sélectionnez les critères pour afficher et gérez vos matières sans chevauchement.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-circle me-2"></i> Ajouter une matière
        </button>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
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
                @foreach($filieres as $filiere)
                    @php $codeF = $filiere->codeFiliere ?? $filiere->code ?? $filiere->id; @endphp
                    <option value="{{ $codeF }}" data-dept="{{ $filiere->departement }}">
                        {{ $filiere->nomFiliere ?? $filiere->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col">
            <label class="form-label small fw-bold text-secondary mb-1">3. Niveau</label>
            <select id="filter-niveau" class="form-select form-select-sm shadow-sm" onchange="updateFiliereBranches()">
                @foreach($niveaux as $niveau)
                    <option value="{{ $niveau->idNiveau }}">{{ $niveau->nom }}</option>
                @endforeach
            </select>
        </div>

        <div class="col">
            <label class="form-label small fw-bold text-secondary mb-1">4. Semestre</label>
            <select id="filter-semestre" class="form-select form-select-sm shadow-sm" onchange="filterTable()">
                </select>
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
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="matiere-id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Code de la matière</label>
                        <input type="text" id="form-code" name="code" class="form-control" placeholder="Ex: WEB2411" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nom de la matière</label>
                        <input type="text" id="form-nom" name="nom" class="form-control" placeholder="Ex: Programmation PHP" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Coefficient</label>
                        <input type="number" id="form-coef" name="coefficient" class="form-control" value="1" min="1" required>
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
    let bootstrapModal;

    document.addEventListener("DOMContentLoaded", function() {
        bootstrapModal = new bootstrap.Modal(document.getElementById('matiereModal'));
        updateFilieres(); 
    });

    async function updateFilieres() {
        const dept = document.getElementById('filter-dept').value;
        const niveau = document.getElementById('filter-niveau').value; 
        const filiereSelect = document.getElementById('filter-filiere');
        const semestreSelect = document.getElementById('filter-semestre');

        // Alignement des semestres
        if (niveau === '1') {
            semestreSelect.innerHTML = '<option value="S1">Semestre 1 (S1)</option><option value="S2">Semestre 2 (S2)</option>';
        } else if (niveau === '2') {
            semestreSelect.innerHTML = '<option value="S3">Semestre 3 (S3)</option><option value="S4">Semestre 4 (S4)</option>';
        } else if (niveau === '3') {
            semestreSelect.innerHTML = '<option value="S5">Semestre 5 (S5)</option><option value="S6">Semestre 6 (S6)</option>';
        } else if (niveau === '4') {
            semestreSelect.innerHTML = '<option value="S7">Semestre 7 (S7)</option><option value="S8">Semestre 8 (S8)</option>';
        } else if (niveau === '5') {
            semestreSelect.innerHTML = '<option value="S9">Semestre 9 (S9)</option><option value="S10">Semestre 10 (S10)</option>';
        } else {
            semestreSelect.innerHTML = '<option value="Thèse">Travaux de Thèse</option>';
        }

        try {
            const response = await fetch(`/admin/filieres/recuperer?dept=${dept}&niveau=${niveau}`);
            const filieresBdd = await response.json();
            
            filiereSelect.innerHTML = '';
            
            if(filieresBdd.length === 0) {
                filiereSelect.innerHTML = '<option value="">Aucune filière trouvée</option>';
            } else {
                filieresBdd.forEach(f => {
                    const codeFiliere = f.codeFiliere ?? f.code ?? f.id;
                    const nomFiliere = f.nomFiliere ?? f.nom;
                    filiereSelect.innerHTML += `<option value="${codeFiliere}">${nomFiliere}</option>`;
                });
            }
        } catch (error) {
            console.error("Impossible de charger les filières de la BDD", error);
        }

        filterTable();
    }

    function updateFiliereBranches() {
        updateFilieres();
    }

    async function filterTable() {
        const dept = document.getElementById('filter-dept').value;
        const filiere = document.getElementById('filter-filiere').value;
        const niveau = document.getElementById('filter-niveau').value;
        const semestre = document.getElementById('filter-semestre').value;
        const tbody = document.getElementById('matieres-tbody');

        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Chargement des matières...</td></tr>`;

        try {
            const response = await fetch(`/admin/matieres/filter?dept=${dept}&filiere=${filiere}&niveau=${niveau}&semestre=${semestre}`);
            const matieres = await response.json();

            tbody.innerHTML = '';

            if (!matieres || matieres.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-exclamation-circle me-2"></i>Aucune matière réelle enregistrée en base pour cette sélection.</td></tr>`;
                return;
            }

            matieres.forEach(m => {
                const id = m.idMatiere ?? m.id;
                const code = m.codeMatiere ?? m.code ?? 'N/A';
                const nom = m.nomMatiere ?? m.nom;
                const coef = m.coefficient ?? m.coef ?? 1;

                tbody.innerHTML += `
                    <tr>
                        <td style="padding-left: 20px;"><span class="badge bg-secondary px-2 py-2">${code}</span></td>
                        <td class="fw-bold text-dark">${nom}</td>
                        <td class="text-center fw-bold">${coef}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning me-2" onclick="openEditModal(${id})">
                                <i class="bi bi-pencil"></i> Modifier
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMatiere(${id})">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error("Erreur de récupération :", error);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4"><i class="bi bi-x-circle me-2"></i>Erreur lors du chargement des matières.</td></tr>`;
        }
    }

    function openAddModal() {
        document.getElementById('modal-title').innerText = "Ajouter une matière";
        document.getElementById('matiere-id').value = '';
        document.getElementById('matiereForm').reset();
        document.getElementById('form-coef').value = '1';
        bootstrapModal.show();
    }

    async function openEditModal(id) {
        try {
            const response = await fetch(`/admin/matieres/${id}/edit`);
            const mat = await response.json();
            
            if(mat) {
                document.getElementById('modal-title').innerText = "Modifier la matière";
                document.getElementById('matiere-id').value = mat.idMatiere ?? mat.id;
                document.getElementById('form-code').value = mat.codeMatiere ?? mat.code;
                document.getElementById('form-nom').value = mat.nomMatiere ?? mat.nom;
                document.getElementById('form-coef').value = mat.coefficient ?? mat.coef;
                bootstrapModal.show();
            }
        } catch (error) {
            alert("Erreur lors de la récupération des détails de la matière.");
        }
    }

    async function saveMatiere(e) {
        e.preventDefault();
        
        const form = document.getElementById('matiereForm');
        const formData = new FormData(form);
        const id = document.getElementById('matiere-id').value;
        
        formData.append('dept', document.getElementById('filter-dept').value);
        formData.append('filiere', document.getElementById('filter-filiere').value);
        formData.append('niveau', document.getElementById('filter-niveau').value);
        formData.append('semestre', document.getElementById('filter-semestre').value);

        // Ajustement des URLs RESTful Laravel standard
        const url = id ? `/admin/matieres/${id}` : '/admin/matieres';
        if(id) {
            formData.append('_method', 'PUT'); // Permet à Laravel de comprendre le PUT avec FormData
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            });
            const result = await response.json();

            if (response.ok && result.success) {
                alert('Matière enregistrée avec succès en base de données !');
                bootstrapModal.hide();
                filterTable(); 
            } else {
                alert("Erreur de sauvegarde : " + JSON.stringify(result.errors));
            }
        } catch (error) {
            console.error("Erreur système :", error);
            alert("Une erreur de communication avec le serveur est survenue.");
        }
    }

    async function deleteMatiere(id) {
        if(confirm("Voulez-vous vraiment supprimer définitivement cette matière ?")) {
            try {
                const response = await fetch(`/admin/matieres/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: (() => { const d = new FormData(); d.append('_method', 'DELETE'); return d; })()
                });
                const result = await response.json();
                
                if (response.ok && result.success) {
                    alert('Matière supprimée avec succès.');
                    filterTable(); 
                } else {
                    alert('Erreur lors de la suppression.');
                }
            } catch (error) {
                console.error("Erreur système lors de la suppression :", error);
            }
        }
    }
</script>
@endsection