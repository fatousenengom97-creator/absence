@extends('layouts.app')
@section('title', 'Étudiants par filière')
@section('page-title', 'Étudiants par filière')

@section('content')
<div class="row g-4">
    @forelse($filieres as $filiere)
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.etudiants-filiere.classes', $filiere) }}" class="text-decoration-none">
            <div class="card h-100" style="transition:.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px;height:50px;background:#ede9fe;color:#7c3aed;">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark">{{ $filiere->nomFiliere }}</h5>
                            <small class="text-muted">{{ $filiere->departement->nomDep ?? '—' }}</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="badge bg-light text-dark">{{ $filiere->classes_count }} classe(s)</span>
                        <span class="text-primary small">Voir les classes <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>Aucune filière trouvée.
        </div></div>
    </div>
    @endforelse
</div>
@endsection