@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h3 class="fw-bold">Rapport Global des Absences par Classe</h3>
       <a href="{{ route('chef.rapport', ['format' => 'pdf']) }}" class="btn btn-danger shadow-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill me-2"></i>Exporter en PDF
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Classe</th>
                            <th class="text-center">Total Pointages</th>
                            <th class="text-center text-success">Présences</th>
                            <th class="text-center text-danger">Absences</th>
                            <th class="text-center">Taux d'absentéisme</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $classe)
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    {{ $classe->filiere->nom ?? 'Filière' }} - {{ $classe->niveau->nom ?? '' }}
                                </td>
                                <td class="text-center fw-bold text-secondary">{{ $classe->total }}</td>
                                <td class="text-center text-success fw-semibold">{{ $classe->presents }}</td>
                                <td class="text-center text-danger fw-semibold">{{ $classe->absents }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress w-50" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $classe->taux > 20 ? 'danger' : 'warning' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $classe->taux }}%"></div>
                                        </div>
                                        <span class="fw-bold">{{ $classe->taux }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Aucune donnée disponible pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection