@extends('layouts.app')
@section('title', 'Nouveau cours')
@section('page-title', 'Créer un cours')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar-plus me-2 text-primary"></i>Nouveau cours
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('cours.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="idMatiere" class="form-select @error('idMatiere') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        @foreach($matieres as $m)
                        <option value="{{ $m->idMatiere }}" {{ old('idMatiere') == $m->idMatiere ? 'selected' : '' }}>
                            {{ $m->nomMatiere }} ({{ $m->codeUE }})
                        </option>
                        @endforeach
                    </select>
                    @error('idMatiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Professeur <span class="text-danger">*</span></label>
                    <select name="professeur_id" class="form-select @error('professeur_id') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        @foreach($professeurs as $p)
                        <option value="{{ $p->id }}" {{ old('professeur_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->user->full_name }} — {{ $p->specialite }}
                        </option>
                        @endforeach
                    </select>
                    @error('professeur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                    <select name="idClasse" class="form-select @error('idClasse') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        @foreach($classes as $cl)
                        <option value="{{ $cl->idClasse }}" {{ old('idClasse') == $cl->idClasse ? 'selected' : '' }}>
                            {{ $cl->nom }} — {{ $cl->filiere->nomFiliere ?? '' }} ({{ $cl->niveau->nom ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('idClasse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
                    <select name="idSalle" class="form-select @error('idSalle') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        @foreach($salles as $s)
                        <option value="{{ $s->idSalle }}" {{ old('idSalle') == $s->idSalle ? 'selected' : '' }}>
                            {{ $s->nom }} (Capacité : {{ $s->capacite }})
                        </option>
                        @endforeach
                    </select>
                    @error('idSalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Heure de début <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="heureDebut"
                           class="form-control @error('heureDebut') is-invalid @enderror"
                           value="{{ old('heureDebut') }}" required>
                    @error('heureDebut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Heure de fin <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="heureFin"
                           class="form-control @error('heureFin') is-invalid @enderror"
                           value="{{ old('heureFin') }}" required>
                    @error('heureFin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jour <span class="text-danger">*</span></label>
                    <select name="jour" class="form-select @error('jour') is-invalid @enderror" required>
                        <option value="">-- Choisir --</option>
                        @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $j)
                        <option value="{{ $j }}" {{ old('jour') === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                    @error('jour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Enregistrer
                </button>
                <a href="{{ route('cours.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection