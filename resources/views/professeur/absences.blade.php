@extends('layouts.app')
@section('title', 'Absences de mes classes')
@section('page-title', 'Absences de mes classes')

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard-x me-2"></i>Absences enregistrées</span>
        <form class="d-flex gap-2" method="GET">
            <input type="date" name="date" class="form-control form-control-sm"
                   value="{{ request('date') }}">
            <button class="btn btn-sm btn-outline-primary">Filtrer</button>
        </form>
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
                        <th>Modifier statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absences as $absence)
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
                        @php
                            $colors = ['present'=>'success','absent'=>'danger','retard'=>'warning','justifie'=>'info'];
                        @endphp
                        <span class="badge bg-{{ $colors[$absence->statut] ?? 'secondary' }}">
                            {{ ucfirst($absence->statut) }}
                        </span>
                    </td>
                    <td>
                        <form method="POST"
                              action="{{ route('professeur.absence.modifier', $absence) }}"
                              class="d-flex gap-1">
                            @csrf @method('PATCH')
                            <select name="statut" class="form-select form-select-sm" style="width:130px;">
                                <option value="present"  {{ $absence->statut==='present'  ?'selected':'' }}>Présent</option>
                                <option value="absent"   {{ $absence->statut==='absent'   ?'selected':'' }}>Absent</option>
                                <option value="retard"   {{ $absence->statut==='retard'   ?'selected':'' }}>Retard</option>
                                <option value="justifie" {{ $absence->statut==='justifie' ?'selected':'' }}>Justifié</option>
                            </select>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-save"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>Aucune absence.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $absences->links() }}</div>
</div>
@endsection