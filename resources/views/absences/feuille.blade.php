@extends('layouts.app')

@section('title', 'Feuille d\'émargement')
@section('page-title', 'Feuille de Présence')

@section('content')
<div class="container-fluid py-4">
    <!-- Retour rapide aux cours -->
    <div class="mb-3">
        <a href="{{ route('cours.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour aux cours
        </a>
    </div>

    <!-- Récapitulatif du Cours -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded shadow-xs">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold text-dark mb-1">
                        {{ $cours->matiere->nomMatiere ?? 'Matière Non Spécifiée' }}
                    </h4>
                    <p class="mb-0 text-muted">
                        <span class="me-3"><i class="bi bi-people-fill me-1"></i>Classe : <strong>{{ $cours->classe->nomClasse ?? $cours->classe->nom ?? '—' }}</strong></span>
                        <span class="me-3"><i class="bi bi-geo-alt-fill me-1"></i>Salle : {{ $cours->salle->nom ?? '—' }}</span>
                        <span><i class="bi bi-calendar-event me-1"></i>Date : {{ \Carbon\Carbon::parse($cours->heureDebut)->translatedFormat('d F Y') }}</span>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-info p-2 px-3 fs-6">
                        <i class="bi bi-clock me-1"></i> 
                        {{ \Carbon\Carbon::parse($cours->heureDebut)->format('H:i') }} - {{ \Carbon\Carbon::parse($cours->heureFin)->format('H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de validation finale -->
    <form action="{{ route('absences.enregistrer', $cours) }}" method="POST">
        @csrf
        
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-check me-2"></i>Liste des étudiants</h5>
                <span class="badge bg-secondary">{{ $cours->absences->count() }} Étudiants au total</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Matricule</th>
                            <th style="width: 35%">Nom Complet</th>
                            <th style="width: 50%" class="text-center">Statut de présence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cours->absences as $absence)
                            <tr>
                                <td>
                                    <code class="fw-bold text-dark">{{ $absence->etudiant->matricule ?? 'MAT-' . $absence->etudiant->id }}</code>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $absence->etudiant->user->name ?? $absence->etudiant->user->nom . ' ' . $absence->etudiant->user->prenom }}
                                    </div>
                                </td>
                                <td>
                                    <!-- Options radio alignées pour chaque étudiant -->
                                    <div class="d-flex justify-content-center gap-3">
                                        
                                        <!-- Présent -->
                                        <input type="radio" class="btn-check" 
                                               name="presences[{{ $absence->etudiant_id }}]" 
                                               id="pres_{{ $absence->etudiant_id }}_present" 
                                               value="present" 
                                               {{ $absence->statut == 'present' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success btn-sm px-3" for="pres_{{ $absence->etudiant_id }}_present">
                                            <i class="bi bi-check-circle me-1"></i> Présent
                                        </label>

                                        <!-- Absent (Sélectionné par défaut via le firstOrCreate) -->
                                        <input type="radio" class="btn-check" 
                                               name="presences[{{ $absence->etudiant_id }}]" 
                                               id="pres_{{ $absence->etudiant_id }}_absent" 
                                               value="absent" 
                                               {{ $absence->statut == 'absent' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger btn-sm px-3" for="pres_{{ $absence->etudiant_id }}_absent">
                                            <i class="bi bi-x-circle me-1"></i> Absent
                                        </label>

                                        <!-- Retard -->
                                        <input type="radio" class="btn-check" 
                                               name="presences[{{ $absence->etudiant_id }}]" 
                                               id="pres_{{ $absence->etudiant_id }}_retard" 
                                               value="retard" 
                                               {{ $absence->statut == 'retard' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning btn-sm px-3" for="pres_{{ $absence->etudiant_id }}_retard">
                                            <i class="bi bi-clock-history me-1"></i> Retard
                                        </label>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    Aucun étudiant inscrit dans cette classe.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Validation -->
            @if($cours->absences->isNotEmpty())
                <div class="card-footer bg-white text-end py-3">
                    <button type="submit" class="btn btn-success px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Clôturer et Enregistrer les présences
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection