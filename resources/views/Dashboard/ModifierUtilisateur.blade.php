@extends('layouts.app')
@section('title', 'Modifier utilisateur')
@section('page-title', 'Modifier un utilisateur')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header">
        <i class="bi bi-pencil me-2 text-warning"></i>
        Modifier — {{ $user->full_name }}
        <span class="badge ms-2
            {{ $user->role === 'etudiant' ? 'bg-success' :
              ($user->role === 'professeur' ? 'bg-primary' :
              ($user->role === 'administrateur' ? 'bg-danger' : 'bg-warning text-dark')) }}">
            {{ ucfirst(str_replace('_',' ',$user->role)) }}
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prénom</label>
                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                           value="{{ old('prenom', $user->prenom) }}" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $user->nom) }}" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                           value="{{ old('telephone', $user->telephone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmer nouveau mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut du compte</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $user->is_active ? 'selected':'' }}>Actif</option>
                        <option value="0" {{ !$user->is_active ? 'selected':'' }}>Inactif</option>
                    </select>
                </div>
            </div>

            @if($user->isEtudiant() && $user->etudiant)
            <hr>
            <h6 class="text-muted fw-semibold mb-3">Informations étudiant</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Code étudiant</label>
                    <input type="text" name="codePar" class="form-control"
                           value="{{ old('codePar', $user->etudiant->codePar) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date de naissance</label>
                    <input type="date" name="dateNaissance" class="form-control"
                           value="{{ old('dateNaissance', $user->etudiant->dateNaissance?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Lieu de naissance</label>
                    <input type="text" name="lieuNaissance" class="form-control"
                           value="{{ old('lieuNaissance', $user->etudiant->lieuNaissance) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Classe</label>
                    <select name="idClasse" class="form-select">
                        @foreach($classes as $cl)
                        <option value="{{ $cl->idClasse }}"
                            {{ $user->etudiant->idClasse == $cl->idClasse ? 'selected':'' }}>
                            {{ $cl->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <a href="{{ route('biometrie.enregistrer', $user->etudiant) }}"
                       class="btn btn-outline-info w-100">
                        <i class="bi bi-camera me-2"></i>
                        {{ $user->etudiant->donneesBiometriques()->exists() ? 'Mettre à jour biométrie' : 'Enregistrer biométrie' }}
                    </a>
                </div>
            </div>
            @endif

            @if($user->isProfesseur() && $user->professeur)
            <hr>
            <h6 class="text-muted fw-semibold mb-3">Informations professeur</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matricule</label>
                    <input type="text" name="matricule" class="form-control"
                           value="{{ old('matricule', $user->professeur->matricule) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Spécialité</label>
                    <input type="text" name="specialite" class="form-control"
                           value="{{ old('specialite', $user->professeur->specialite) }}">
                </div>
            </div>
            @endif

            <hr class="my-4">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save me-2"></i>Enregistrer les modifications
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection