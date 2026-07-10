@extends('layouts.app')

@section('content')
<div class="container-fluid px-4" style="padding-left: 15px;">
    <div class="d-flex justify-content-between align-items-center my-4 pt-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Planification des Cours</h1>
            <p class="text-muted small d-none d-md-block">Gérez l'emploi du temps en associant les classes, les matières, les professeurs et les salles.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#coursModal">
            <i class="bi bi-calendar-plus me-2"></i> Planifier un cours
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4 border-0 bg-light">
        <div class="card-body p-3">
            <form method="GET" action="{{ auth()->user()->role === 'administrateur' ? route('admin.cours.index') : route('cours.index') }}" id="filterForm">
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <div class="col">
                        <label class="form-label small fw-bold text-secondary mb-1">1. Sélectionner la Classe</label>
                        <select name="classe_id" id="filter-classe" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- Choisir la classe --</option>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->idClasse }}" {{ $classeSelectionnee == $classe->idClasse ? 'selected' : '' }}>
                                    {{ $classe->nom }} 
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label small fw-bold text-secondary mb-1">2. Semestre</label>
                        <select name="semestre" id="filter-semestre" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- Choisir le semestre --</option>
                            @foreach(['S1', 'S2', 'S3', 'S4', 'S5', 'S6'] as $sem)
                                <option value="{{ $sem }}" {{ ($semestreSelectionne ?? $semestreSelectionnee ?? '') == $sem ? 'selected' : '' }}>
                                    Semestre {{ substr($sem, 1) }} ({{ $sem }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
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
                        @if($cours->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x me-2"></i>Aucun cours réel programmé pour cette sélection.
                                </td>
                            </tr>
                        @else
                            @foreach($cours as $c)
                                <tr>
                                    <td style="padding-left: 20px;" class="fw-bold text-secondary">{{ $c->jour }}</td>
                                    <td class="text-primary fw-bold">
                                        <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }} - {{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}
                                    </td>
                                    <td class="fw-bold text-dark">{{ $c->matiere->nomMatiere ?? 'Matière inconnue' }}</td>
                                    <td><span class="text-muted"><i class="bi bi-person-badge me-1"></i>{{ $c->professeur->user->prenom ?? '' }} {{ $c->professeur->user->nom ?? '' }}</span></td>
                                    <td><span class="badge bg-light text-primary border border-primary px-2 py-1 fw-bold">{{ $c->salle->nom ?? 'N/A' }}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" title="Annuler">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
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
                <h5 class="modal-title">Planifier un nouveau cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            @if(auth()->user()->role === 'administrateur')
                <form action="{{ route('admin.cours.store') }}" method="POST">
                    @csrf
                    
                    <div class="p-3 bg-light border-bottom">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-secondary mb-1">Classe ciblée</label>
                                @if(!empty($classeSelectionnee))
                                    <input type="hidden" name="idClasse" value="{{ $classeSelectionnee }}">
                                    <input type="text" class="form-control form-control-sm bg-white" 
                                           value="{{ $classes->firstWhere('idClasse', $classeSelectionnee)->nom ?? 'Classe Sélectionnée' }}" readonly>
                                @else
                                    <select name="idClasse" class="form-select form-select-sm" required>
                                        <option value="">-- Choisir la classe --</option>
                                        @foreach($classes as $classe)
                                            <option value="{{ $classe->idClasse }}">{{ $classe->nom }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-secondary mb-1">Semestre</label>
                                @php 
                                    $currentSem = $semestreSelectionne ?? $semestreSelectionnee ?? ''; 
                                @endphp
                                @if(!empty($currentSem))
                                    <input type="hidden" name="semestre" value="{{ $currentSem }}">
                                    <input type="text" class="form-control form-control-sm bg-white" value="{{ $currentSem }}" readonly>
                                @else
                                    <select name="semestre" class="form-select form-select-sm" required>
                                        <option value="">-- Choisir le semestre --</option>
                                        @foreach(['S1', 'S2', 'S3', 'S4', 'S5', 'S6'] as $sem)
                                            <option value="{{ $sem }}">{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Jour</label>
                                <select name="jour" class="form-select" required>
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
                                <input type="time" name="heureDebut" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Heure Fin</label>
                                <input type="time" name="heureFin" class="form-control" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Matière</label>
                                <select name="matiere_id" class="form-select" required>
                                    <option value="">-- Choisir la matière --</option>
                                    @foreach($matieres as $matiere)
                                        <option value="{{ $matiere->id }}">{{ $matiere->nomMatiere }} ({{ $matiere->code ?? 'SATIC' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Enseignant</label>
                                <select name="professeur_id" class="form-select" required>
                                    <option value="">-- Choisir l'enseignant --</option>
                                    @foreach($professeurs as $prof)
                                        <option value="{{ $prof->id }}">M. {{ $prof->user->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Salle de cours</label>
                                <select name="salle_id" class="form-select" required>
                                    <option value="">-- Choisir la salle --</option>
                                    @foreach($salles as $salle)
                                        <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer le cours</button>
                    </div>
                </form>
            @else
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Accès restreint :</strong> Seul le personnel administratif est autorisé à planifier, attribuer des salles ou configurer de nouveaux créneaux de cours.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection