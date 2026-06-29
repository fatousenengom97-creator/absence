@extends('layouts.app')
@section('title', 'Créer utilisateur')
@section('page-title', 'Créer un utilisateur')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header"><i class="bi bi-person-plus me-2 text-primary"></i>Nouvel utilisateur</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" id="formUser">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                           value="{{ old('prenom') }}" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom') }}" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmer mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                    <select name="role" id="selectRole" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        <option value="etudiant"       {{ old('role')==='etudiant'       ? 'selected':'' }}>Étudiant</option>
                        <option value="professeur"     {{ old('role')==='professeur'     ? 'selected':'' }}>Professeur</option>
                        <option value="chef_service"   {{ old('role')==='chef_service'   ? 'selected':'' }}>Chef de service</option>
                        <option value="administrateur" {{ old('role')==='administrateur' ? 'selected':'' }}>Administrateur</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Champs Étudiant : cascade Département -> Filière -> Classe --}}
            <div id="champsEtudiant" class="row g-3 mt-1" style="display:none!important;">
                <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2 mt-3">Informations étudiant</h6></div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Code étudiant</label>
                    <input type="text" name="codePar" class="form-control" value="{{ old('codePar') }}"
                           placeholder="Auto si vide">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date de naissance</label>
                    <input type="date" name="dateNaissance" class="form-control" value="{{ old('dateNaissance') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Lieu de naissance</label>
                    <input type="text" name="lieuNaissance" class="form-control" value="{{ old('lieuNaissance') }}">
                </div>

                {{-- ÉTAPE 1 : Département --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>Département <span class="text-danger">*</span>
                    </label>
                    <select id="selectDepartement" class="form-select">
                        <option value="">-- Choisir un département --</option>
                        @foreach($departements as $dep)
                        <option value="{{ $dep->idDep }}">{{ $dep->nomDep }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ÉTAPE 2 : Filière (rempli dynamiquement) --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-diagram-3 me-1"></i>Filière <span class="text-danger">*</span>
                    </label>
                    <select id="selectFiliere" class="form-select" disabled>
                        <option value="">-- Choisir d'abord le département --</option>
                    </select>
                </div>

                {{-- ÉTAPE 3 : Classe (rempli dynamiquement) --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-people me-1"></i>Classe <span class="text-danger">*</span>
                    </label>
                    <select name="idClasse" id="selectClasse" class="form-select @error('idClasse') is-invalid @enderror" disabled>
                        <option value="">-- Choisir d'abord la filière --</option>
                    </select>
                    @error('idClasse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div id="classeConfirmee" class="alert alert-success d-none">
                        <i class="bi bi-check-circle me-2"></i>
                        Étudiant inscrit dans : <strong id="classeConfirmeeTexte"></strong>
                    </div>
                </div>
            </div>

            {{-- Champs Professeur --}}
            <div id="champsProfesseur" class="row g-3 mt-1" style="display:none!important;">
                <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2 mt-3">Informations professeur</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matricule</label>
                    <input type="text" name="matricule" class="form-control" value="{{ old('matricule') }}"
                           placeholder="Auto si vide">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Spécialité</label>
                    <input type="text" name="specialite" class="form-control" value="{{ old('specialite') }}">
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-check me-2"></i>Créer l'utilisateur
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const selectRole        = document.getElementById('selectRole');
const champsEtudiant     = document.getElementById('champsEtudiant');
const champsProfesseur   = document.getElementById('champsProfesseur');
const selectDepartement  = document.getElementById('selectDepartement');
const selectFiliere      = document.getElementById('selectFiliere');
const selectClasse       = document.getElementById('selectClasse');
const classeConfirmee    = document.getElementById('classeConfirmee');
const classeConfirmeeTxt = document.getElementById('classeConfirmeeTexte');

function toggleChamps() {
    const role = selectRole.value;
    champsEtudiant.style.display   = role === 'etudiant'   ? 'flex' : 'none';
    champsProfesseur.style.display = role === 'professeur' ? 'flex' : 'none';
}
selectRole.addEventListener('change', toggleChamps);
toggleChamps();

selectDepartement.addEventListener('change', async function () {
    const depId = this.value;
    selectFiliere.innerHTML = '<option value="">Chargement...</option>';
    selectFiliere.disabled = true;
    selectClasse.innerHTML = '<option value="">-- Choisir d\'abord la filière --</option>';
    selectClasse.disabled = true;
    classeConfirmee.classList.add('d-none');

    if (!depId) {
        selectFiliere.innerHTML = '<option value="">-- Choisir d\'abord le département --</option>';
        return;
    }

    const res = await fetch(`/admin/api/departements/${depId}/filieres`);
    const filieres = await res.json();

    selectFiliere.innerHTML = '<option value="">-- Choisir une filière --</option>';
    filieres.forEach(f => {
        const opt = document.createElement('option');
        opt.value = f.idFiliere;
        opt.textContent = f.nomFiliere;
        selectFiliere.appendChild(opt);
    });
    selectFiliere.disabled = false;
});

selectFiliere.addEventListener('change', async function () {
    const filiereId = this.value;
    selectClasse.innerHTML = '<option value="">Chargement...</option>';
    selectClasse.disabled = true;
    classeConfirmee.classList.add('d-none');

    if (!filiereId) {
        selectClasse.innerHTML = '<option value="">-- Choisir d\'abord la filière --</option>';
        return;
    }

    const res = await fetch(`/admin/api/filieres/${filiereId}/classes`);
    const classes = await res.json();

    selectClasse.innerHTML = '<option value="">-- Choisir une classe --</option>';
    classes.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.idClasse;
        opt.textContent = c.nom;
        selectClasse.appendChild(opt);
    });
    selectClasse.disabled = false;
});

selectClasse.addEventListener('change', function () {
    if (this.value) {
        classeConfirmeeTxt.textContent = this.options[this.selectedIndex].textContent;
        classeConfirmee.classList.remove('d-none');
    } else {
        classeConfirmee.classList.add('d-none');
    }
});
</script>
@endpush