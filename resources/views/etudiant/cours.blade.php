@extends('layouts.app')
@section('title', 'Mes cours')
@section('page-title', 'Mes cours — {{ $classe?->nom ?? "" }}')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-book me-2"></i>Tous mes cours
        @if($classe)<span class="badge bg-info ms-2">{{ $classe->nom }}</span>@endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Matière</th>
                        <th>Professeur</th>
                        <th>Salle</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Type</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                <tr>
                    <td><small>{{ \Carbon\Carbon::parse($c->heureDebut)->format('d/m/Y') }}</small></td>
                    <td><strong>{{ $c->matiere->nomMatiere ?? '—' }}</strong></td>
                    <td><small>{{ $c->professeur->user->prenom ?? '—' }} {{ $c->professeur->user->nom ?? '' }}</small></td>
                    <td><small>{{ $c->salle->nom ?? '—' }}</small></td>
                    <td><span class="badge bg-primary">{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</span></td>
                    <td><span class="badge bg-secondary">{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</span></td>
                    <td><span class="badge bg-dark">{{ $c->typeCours }}</span></td>
                    <td>
                        @php $colors = ['planifie'=>'secondary','en_cours'=>'success','termine'=>'dark','annule'=>'danger']; @endphp
                        <span class="badge bg-{{ $colors[$c->statut] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_',' ',$c->statut)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-book fs-2 d-block mb-2"></i>Aucun cours trouvé.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $cours->links() }}</div>
</div>
@endsection