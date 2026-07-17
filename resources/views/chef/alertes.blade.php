@extends('layouts.app')
@section('title', 'Alertes absences')
@section('page-title', 'Alertes — Modifications de statuts')

@section('content')
<div class="card">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-bell me-2"></i>Modifications récentes de statuts d'absence
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Date</th>
                        <th>Statut actuel</th>
                        <th>Modifié le</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absencesModifiees as $absence)
                <tr>
                    <td>
                        <div class="fw-semibold small">
                            {{ $absence->etudiant->user->prenom ?? '—' }}
                            {{ $absence->etudiant->user->nom ?? '' }}
                        </div>
                    </td>
                    <td><small>{{ $absence->cours->matiere->nomMatiere ?? '—' }}</small></td>
                    <td><small>{{ $absence->cours->classe->nom ?? '—' }}</small></td>
                    <td><small>{{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</small></td>
                    <td>
                        @php $colors=['present'=>'success','absent'=>'danger','retard'=>'warning','justifie'=>'info']; @endphp
                        <span class="badge bg-{{ $colors[$absence->statut]??'secondary' }}">
                            {{ ucfirst($absence->statut) }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $absence->updated_at->format('d/m/Y H:i') }}</small></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                    Aucune modification récente.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection