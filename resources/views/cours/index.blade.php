@extends('layouts.app')
@section('title', 'Mes cours')
@section('page-title', 'Mes cours')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('cours.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nouveau cours
    </a>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-book me-2"></i>Liste des cours</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Salle</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Heure début</th>
                        <th>Heure fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cours as $c)
                <tr>
                    <td><strong>{{ $c->matiere->nomMatiere ?? '—' }}</strong></td>
                    <td><small>{{ $c->classe->nom ?? '—' }}</small></td>
                    <td><small>{{ $c->salle->nom ?? '—' }}</small></td>
                    <td><span class="badge bg-secondary">{{ $c->typeCours }}</span></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->heureDebut)->format('d/m/Y') }}</small></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }}</small></td>
                    <td><small>{{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</small></td>
                    <td>
                        @php
                            $colors = [
                                'planifie'  => 'secondary',
                                'en_cours'  => 'success',
                                'termine'   => 'dark',
                                'annule'    => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $colors[$c->statut] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $c->statut)) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($c->statut === 'planifie')
                            <form method="POST" action="{{ route('cours.demarrer', $c) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Démarrer le cours">
                                    <i class="bi bi-play-fill"></i> Démarrer
                                </button>
                            </form>
                            @endif

                            @if($c->statut === 'en_cours')
                            <a href="{{ route('biometrie.pointage', $c) }}" class="btn btn-sm btn-primary" title="Pointage facial">
                                <i class="bi bi-camera"></i> Pointage
                            </a>
                            <form method="POST" action="{{ route('cours.terminer', $c) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-dark" title="Terminer le cours">
                                    <i class="bi bi-stop-fill"></i> Terminer
                                </button>
                            </form>
                            @endif

                            @if($c->statut === 'termine')
                            <a href="{{ route('absences.feuille', $c) }}" class="btn btn-sm btn-info text-white" title="Voir absences">
                                <i class="bi bi-clipboard-check"></i> Absences
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-book fs-2 d-block mb-2"></i>Aucun cours trouvé.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cours->hasPages())
    <div class="card-footer">
        {{ $cours->links() }}
    </div>
    @endif
</div>
@endsection