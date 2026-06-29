@extends('layouts.app')
@section('title', 'Rapport global')
@section('page-title', 'Rapport global des absences')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('chef.rapport', ['format'=>'pdf']) }}" class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf me-2"></i>Exporter PDF
    </a>
</div>

<div class="row g-4 mb-4">
    @foreach($classes as $cl)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $cl->nom }}</h6>
                        <small class="text-muted">{{ $cl->filiere->nomFiliere ?? '—' }} / {{ $cl->niveau->nom ?? '—' }}</small>
                    </div>
                    <span class="badge rounded-pill
                        {{ $cl->taux > 20 ? 'bg-danger' : ($cl->taux > 10 ? 'bg-warning text-dark' : 'bg-success') }}
                        fs-6">
                        {{ $cl->taux }}%
                    </span>
                </div>

                {{-- Barre de progression --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Taux d'absence</span>
                        <span>{{ $cl->absents }} / {{ $cl->total }}</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;">
                        <div class="progress-bar
                            {{ $cl->taux > 20 ? 'bg-danger' : ($cl->taux > 10 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ min($cl->taux, 100) }}%"></div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="fw-bold text-success">{{ $cl->presents }}</div>
                        <div class="text-muted" style="font-size:.7rem;">Présences</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-danger">{{ $cl->absents }}</div>
                        <div class="text-muted" style="font-size:.7rem;">Absences</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-primary">{{ $cl->total }}</div>
                        <div class="text-muted" style="font-size:.7rem;">Total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection