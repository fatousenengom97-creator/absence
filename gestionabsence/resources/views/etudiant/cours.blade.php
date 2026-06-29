@extends('layouts.app')
@section('title', 'Mes cours')
@section('page-title', 'Mes cours')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar3 me-2"></i>Liste de mes cours ({{ $cours->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Professeur</th>
                        <th>Salle</th>
                        <th>Début</th>
                        <th>Fin</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                    <tr>
                        <td class="fw-semibold small">{{ $c->matiere->nomMatiere ?? '—' }}</td>
                        <td><small>{{ $c->professeur->user->prenom ?? '' }} {{ $c->professeur->user->nom ?? '—' }}</small></td>
                        <td><small>{{ $c->salle->nom ?? '—' }}</small></td>
                        <td><small>{{ \Illuminate\Support\Carbon::parse($c->heureDebut)->format('d/m/Y H:i') }}</small></td>
                        <td><small>{{ $c->heureFin ? \Illuminate\Support\Carbon::parse($c->heureFin)->format('d/m/Y H:i') : '—' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun cours trouvé.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $cours->links() }}</div>
</div>
@endsection