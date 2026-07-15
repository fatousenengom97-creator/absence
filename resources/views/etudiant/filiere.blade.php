@extends('layouts.app')
@section('title', 'Étudiants par Filière')
@section('page-title', 'Étudiants par Filière')

@section('content')
<div class="row">
    @forelse($filieres as $filiere)
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold mb-0 text-dark">{{ $filiere->nomFiliere }}</h5>
                                <span class="badge bg-secondary mt-1">Département #{{ $filiere->idDep }}</span>
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-3">
                            Cette filière regroupe actuellement <strong>{{ $filiere->classes_count }}</strong> classe(s) active(s).
                        </p>
                    </div>

                    <div class="pt-3 border-top border-light">
                        <a href="#" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-eye me-2"></i> Voir les étudiants
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted mb-3">
                <i class="bi bi-folder-x fs-1 text-secondary"></i>
            </div>
            <h4 class="fw-semibold text-secondary">Aucune filière trouvée</h4>
            <p class="text-muted">Ajoutez des filières depuis votre panneau d'administration pour commencer.</p>
        </div>
    @endforelse
</div>
@endsection