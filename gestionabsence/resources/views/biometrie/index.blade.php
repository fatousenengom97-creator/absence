@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Gestion des Empreintes Faciales</h2>
            <p class="text-muted">Suivi du statut de l'enrôlement biométrique des étudiants.</p>
        </div>
    </div>

    <!-- Carte principale -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h5 class="card-title fw-semibold mb-3">Liste des étudiants</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom complet</th>
                            <th>Classe</th>
                            <th>Statut Biométrique</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($etudiants as $etudiant)
                            @php
                                $hasBio = $etudiant->donneesBiometriques->isNotEmpty();
                            @endphp
                            <tr>
                                <td class="fw-bold text-secondary">{{ $etudiant->codePar }}</td>
                                <td>{{ $etudiant->full_name }}</td>
                                <td>
                                    <span class="badge bg-secondary opacity-75">
                                        {{ $etudiant->classe?->nom ?? 'Non assignée' }}
                                    </span>
                                </td>
                                <td>
                                    @if($hasBio)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1.5">
                                            <i class="fas fa-check-circle me-1"></i> Enrôlé
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1.5">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Manquant
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
    <!-- Utilisation de ta vraie route : biometrie.enregistrer -->
    <a href="{{ route('biometrie.enregistrer', ['etudiant' => $etudiant->id]) }}" 
       class="btn btn-sm {{ $hasBio ? 'btn-outline-primary' : 'btn-primary shadow-sm' }}">
        <i class="fas fa-camera me-1"></i> 
        {{ $hasBio ? 'Réenrôler' : 'Enrôler' }}
    </a>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Aucun étudiant trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Laravel -->
            <div class="mt-3">
                {{ $etudiants->links() }}
            </div>
        </div>
    </div>
</div>
@endsection