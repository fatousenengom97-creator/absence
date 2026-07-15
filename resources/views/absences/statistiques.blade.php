@extends('layouts.app')
@section('title', 'Statistiques')
@section('page-title', 'Statistiques des absences')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-bar-chart me-2"></i>Taux d'absence par classe
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th>Filière</th>
                        <th>Niveau</th>
                        <th>Étudiants</th>
                        <th>Total absences</th>
                        <th>Taux d'absence</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($classes as $classe)
                <tr>
                    <td><strong>{{ $classe->nom }}</strong></td>
                    <td><small class="text-muted">{{ $classe->filiere->nomFiliere ?? '—' }}</small></td>
                    <td><span class="badge bg-info">{{ $classe->niveau->nom ?? '—' }}</span></td>
                    <td>{{ $classe->etudiants->count() }}</td>
                    <td>{{ $classe->taux_absence }}</td>
                    <td>
                        @php $taux = $classe->taux_absence; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar {{ $taux > 30 ? 'bg-danger' : ($taux > 15 ? 'bg-warning' : 'bg-success') }}"
                                     style="width:{{ min($taux, 100) }}%"></div>
                            </div>
                            <span class="small">{{ $taux }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Aucune donnée.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection