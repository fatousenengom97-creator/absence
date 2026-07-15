@extends('layouts.app')
@section('title', 'Absences')
@section('page-title', 'Gestion des absences')

@section('content')
{{-- Filtres Dynamiques --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="present"  {{ request('statut')=='present'  ? 'selected' : '' }}>Présent</option>
                    <option value="absent"   {{ request('statut')=='absent'   ? 'selected' : '' }}>Absent</option>
                    <option value="retard"   {{ request('statut')=='retard'   ? 'selected' : '' }}>Retard</option>
                    <option value="justifie" {{ request('statut')=='justifie' ? 'selected' : '' }}>Justifié</option>
                </select>
            </div>

            @if(!auth()->user()->isEtudiant())
                {{-- Filtre Filière --}}
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Filière</label>
                    <select name="filiere" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($filieres as $f)
                            <option value="{{ $f->idFiliere ?? $f->id }}" {{ request('filiere') == ($f->idFiliere ?? $f->id) ? 'selected' : '' }}>
                                {{ $f->nomFiliere }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Filtre Classe --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Classe / Niveau</label>
                    <select name="classe" class="form-select">
                        <option value="">Toutes les classes</option>
                        @foreach($filieres as $f)
                            <optgroup label="{{ $f->nomFiliere }}">
                                @foreach($f->classes as $cl)
                                    <option value="{{ $cl->idClasse ?? $cl->id }}" {{ request('classe') == ($cl->idClasse ?? $cl->id) ? 'selected' : '' }}>
                                        [{{ $cl->niveau->nom ?? 'N/A' }}] {{ $cl->nomClasse ?? $cl->nom }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-3 d-flex">
                <button type="submit" class="btn btn-primary me-2 w-100">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('absences.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Affichage des Absences --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="fw-bold text-secondary fs-5">
        <i class="bi bi-clipboard-data me-2"></i>Liste des fiches d'absences ({{ $absences->total() }})
    </span>
    <a href="{{ route('absences.rapport', request()->all()) }}" class="btn btn-sm btn-danger shadow-sm">
        <i class="bi bi-file-pdf me-1"></i>Exporter PDF
    </a>
</div>

@if(auth()->user()->isEtudiant() || !$absencesGroupees || $absencesGroupees->isEmpty())
    {{-- Vue Standard (Pour Étudiant ou si aucun résultat disponible) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            @include('absences.partials.table', ['listeAbsences' => $absences])
        </div>
    </div>
@else
    {{-- Vue Réorganisée en Arbre : Filière -> Niveau -> Classe --}}
    @foreach($absencesGroupees as $filiereNom => $niveaux)
        <div class="border-start border-primary border-3 ps-3 mb-4 mt-2">
            <h3 class="text-primary fw-bold mb-0"><i class="bi bi-building me-2"></i>{{ $filiereNom }}</h3>
        </div>

        @foreach($niveaux as $niveauNom => $classes)
            <div class="ms-3 mb-3">
                <h5 class="text-secondary fw-semibold mb-2"><i class="bi bi-layers me-2"></i>{{ $niveauNom }}</h5>
                
                @foreach($classes as $classeNom => $listeAbsences)
                    <div class="card shadow-sm border-0 mb-3 ms-3">
                        <div class="card-header bg-light py-2">
                            <span class="fw-bold text-dark small"><i class="bi bi-mortarboard me-2"></i>Classe : {{ $classeNom }}</span>
                        </div>
                        <div class="card-body p-0">
                            @include('absences.partials.table', ['listeAbsences' => $listeAbsences])
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endforeach
@endif

<div class="d-flex justify-content-center py-3">
    {{ $absences->withQueryString()->links() }}
</div>
@endsection