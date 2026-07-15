@extends('layouts.app')
@section('title', $filiere->nomFiliere)
@section('page-title', 'Classes — ' . $filiere->nomFiliere)

@section('content')
<nav class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.etudiants-filiere.index') }}">Filières</a></li>
        <li class="breadcrumb-item active">{{ $filiere->nomFiliere }}</li>
    </ol>
</nav>

<div class="row g-4">
    @forelse($classesParNiveau as $nomNiveau => $classes)
    <div class="col-12">
        <h6 class="text-muted fw-semibold mb-3">
            <span class="badge bg-info">{{ $nomNiveau }}</span>
        </h6>
        <div class="row g-3 mb-3">
            @foreach($classes as $classe)
            <div class="col-md-4 col-lg-3">
                <a href="{{ route('admin.etudiants-filiere.etudiants', [$filiere, $classe]) }}" class="text-decoration-none">
                    <div class="card h-100" style="transition:.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-body text-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                 style="width:50px;height:50px;background:#d1fae5;color:#065f46;">
                                <i class="bi bi-mortarboard fs-4"></i>
                            </div>
                            <h6 class="mb-1 text-dark">{{ $classe->nom }}</h6>
                            <small class="text-muted">{{ $classe->etudiants_count }} étudiant(s)</small>
                            <div class="mt-2">
                                <span class="text-success small">Voir la liste <i class="bi bi-arrow-right"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>Aucune classe dans cette filière.
        </div></div>
    </div>
    @endforelse
</div>
@endsection