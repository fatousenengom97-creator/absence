@extends('layouts.app')
@section('title', 'Emplois du temps')
@section('page-title', 'Gestion des emplois du temps')

@section('content')
<div class="row g-4">
    @forelse($classes as $classe)
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('chef.edt.classe', $classe) }}" class="text-decoration-none">
            <div class="card h-100" style="transition:.2s;cursor:pointer;"
                 onmouseover="this.style.transform='translateY(-3px)'"
                 onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px;height:50px;background:#ede9fe;color:#7c3aed;">
                            <i class="bi bi-calendar-week fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark">{{ $classe->nom }}</h5>
                            <small class="text-muted">{{ $classe->filiere->nomFiliere ?? '—' }}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-primary small">Gérer l'EDT <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            Aucune classe trouvée.
        </div></div>
    </div>
    @endforelse
</div>
@endsection