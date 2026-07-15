@extends('layouts.app')
@section('title', 'Nouveau cours')
@section('page-title', 'Planifier un cours')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle me-2 text-primary"></i>Nouveau cours</div>
    <div class="card-body">
        <form method="POST" action="{{ route('cours.store') }}">
            @csrf
            <div class="row g-3">

                {{-- Professeur --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Professeur <span class="text-danger">*</span></label>
                    <select name="professeur_id" class="form-select @error('professeur_id') is-invalid @enderror" required>
                        <option value="">-- Choisir un professeur --</option>
                        @foreach($professeurs as $prof)
                        <option value="{{ $prof->id }}" {{ old('professeur_id') == $prof->id ? 'selected' : '' }}>
                            {{ $prof->user->prenom }} {{ $prof->user->nom }} — {{ $prof->specialite }}
                        </option>
                        @endforeach
                    </select>
                    @error('professeur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Matière --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="idMatiere" class="form-select @error('idMatiere') is-invalid @enderror" required>
                        <option value="">-- Choisir une matière --</option>
                        @foreach($matieres as $matiere)
                        <option value="{{ $matiere->idMatiere }}" {{ old('idMatiere') == $matiere->idMatiere ? 'selected' : '' }}>
                            {{ $matiere->nomMatiere }}
                        </option>
                        @endforeach
                    </select>
                    @error('idMatiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Classe --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                    <select name="idClasse" class="form-select @error('idClasse') is-invalid @enderror" required>
                        <option value="">-- Choisir une classe --</option>
                        @foreach($classes as $classe)
                        <option value="{{ $classe->idClasse }}" {{ old('idClasse') == $classe->idClasse ? 'selected' : '' }}>
                            {{ $classe->nom }}
                            @if($classe->filiere) — {{ $classe->filiere->nomFiliere }} @endif
                        </option>
                        @endforeach
                    </select>
                    @error('idClasse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Salle --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
                    <select name="idSalle" class="form-select @error('idSalle') is-invalid @enderror" required>
                        <option value="">-- Choisir une salle --</option>
                        @foreach($salles as $salle)
                        <option value="{{ $salle->idSalle }}" {{ old('idSalle') == $salle->idSalle ? 'selected' : '' }}>
                            {{ $salle->nom }} ({{ $salle->capacite }} places)
                        </option>
                        @endforeach
                    </select>
                    @error('idSalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Type de cours --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="typeCours" class="form-select" required>
                        <option value="CM" {{ old('typeCours', 'CM') === 'CM' ? 'selected' : '' }}>CM — Cours Magistral</option>
                        <option value="TD" {{ old('typeCours') === 'TD' ? 'selected' : '' }}>TD — Travaux Dirigés</option>
                        <option value="TP" {{ old('typeCours') === 'TP' ? 'selected' : '' }}>TP — Travaux Pratiques</option>
                    </select>
                </div>

                {{-- Date --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                           value="{{ old('date', today()->format('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Heure début --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Début <span class="text-danger">*</span></label>
                    <input type="time" name="heureDebut" class="form-control"
                           value="{{ old('heureDebut', '07:30') }}" required>
                </div>

                {{-- Heure fin --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
                    <input type="time" name="heureFin" class="form-control"
                           value="{{ old('heureFin', '09:00') }}" required>
                </div>

            </div>

            <hr class="my-4">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Créer le cours
                </button>
                <a href="{{ route('cours.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection