@extends('layouts.app')
@section('title', 'Mes absences')
@section('page-title', 'Mes absences')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('etudiant.absences') }}" class="row g-2">
            <div class="col-auto">
                <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="present" {{ request('statut') === 'present' ? 'selected' : '' }}>Présent</option>
                    <option value="absent" {{ request('statut') === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="retard" {{ request('statut') === 'retard' ? 'selected' : '' }}>Retard</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date" value="{{ request('date') }}"
                       class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            @if(request('statut') || request('date'))
            <div class="col-auto">
                <a href="{{ route('etudiant.absences') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-clipboard-data me-2"></i>Liste de mes absences ({{ $absences->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absences as $a)
                    <tr>
                        <td class="fw-semibold small">{{ $a->cours->matiere->nomMatiere ?? '—' }}</td>
                        <td>
                            @php $type = $a->cours->typeCours ?? null; @endphp
                            @if($type === 'CM')
                                <span class="badge bg-primary">CM</span>
                            @elseif($type === 'TD')
                                <span class="badge bg-info text-dark">TD</span>
                            @elseif($type === 'TP')
                                <span class="badge bg-warning text-dark">TP</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><small>{{ $a->cours?->heureDebut ? \Illuminate\Support\Carbon::parse($a->cours->heureDebut)->format('d/m/Y H:i') : '—' }}</small></td>
                        <td><small>{{ $a->cours?->heureFin ? \Illuminate\Support\Carbon::parse($a->cours->heureFin)->format('d/m/Y H:i') : '—' }}</small></td>
                        <td>
                            @if($a->statut === 'present')
                                <span class="badge bg-success rounded-pill">Présent</span>
                            @elseif($a->statut === 'absent')
                                <span class="badge bg-danger rounded-pill">Absent</span>
                            @elseif($a->statut === 'retard')
                                <span class="badge bg-warning text-dark rounded-pill">Retard</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">{{ $a->statut }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>Aucune absence trouvée.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $absences->links() }}</div>
</div>
@endsection