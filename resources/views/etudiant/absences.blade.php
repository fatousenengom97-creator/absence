@extends('layouts.app')
@section('title', 'Mes absences')
@section('page-title', 'Mes absences')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-clipboard-x fs-1 text-danger"></i>
                <h4 class="mt-2">{{ $absences->where('statut','absent')->count() }}</h4>
                <small class="text-muted">Absences</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-clock-history fs-1 text-warning"></i>
                <h4 class="mt-2">{{ $absences->where('statut','retard')->count() }}</h4>
                <small class="text-muted">Retards</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-patch-check fs-1 text-success"></i>
                <h4 class="mt-2">{{ $absences->where('statut','justifie')->count() }}</h4>
                <small class="text-muted">Justifiées</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clipboard-x me-2"></i>Historique de mes absences</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absences as $absence)
                <tr>
                    <td><small>{{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</small></td>
                    <td><strong>{{ $absence->cours->matiere->nomMatiere ?? '—' }}</strong></td>
                    <td><small>{{ $absence->cours->classe->nom ?? '—' }}</small></td>
                    <td>
                        @php
                            $colors = ['present'=>'success','absent'=>'danger','retard'=>'warning','justifie'=>'info'];
                        @endphp
                        <span class="badge bg-{{ $colors[$absence->statut] ?? 'secondary' }}">
                            {{ ucfirst($absence->statut) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-5">
                    <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>Aucune absence enregistrée.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $absences->links() }}</div>
</div>
@endsection