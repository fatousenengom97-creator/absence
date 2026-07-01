@extends('layouts.app')
@section('title', 'Détail cours')
@section('page-title', 'Détail du cours')

@section('content')
<div class="row g-4">
    {{-- Infos cours --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted small">Matière</dt>
                    <dd class="col-7 small fw-semibold">{{ $cours->matiere->nomMatiere }}</dd>

                    <dt class="col-5 text-muted small">Code UE</dt>
                    <dd class="col-7"><code class="small">{{ $cours->matiere->codeUE ?? '—' }}</code></dd>

                    <dt class="col-5 text-muted small">Professeur</dt>
                    <dd class="col-7 small">{{ $cours->professeur->user->full_name }}</dd>

                    <dt class="col-5 text-muted small">Classe</dt>
                    <dd class="col-7 small">{{ $cours->classe->nom }}</dd>

                    <dt class="col-5 text-muted small">Salle</dt>
                    <dd class="col-7 small">{{ $cours->salle->nom }}</dd>

                    <dt class="col-5 text-muted small">Jour</dt>
                    <dd class="col-7 small">{{ $cours->jour }}</dd>

                    <dt class="col-5 text-muted small">Début</dt>
                    <dd class="col-7 small">{{ $cours->heureDebut->format('d/m/Y H:i') }}</dd>

                    <dt class="col-5 text-muted small">Fin</dt>
                    <dd class="col-7 small">{{ $cours->heureFin->format('H:i') }}</dd>

                    <dt class="col-5 text-muted small">Statut</dt>
                    <dd class="col-7">
                        <span class="badge rounded-pill
                            {{ $cours->statut === 'en_cours' ? 'bg-success' :
                              ($cours->statut === 'termine'  ? 'bg-secondary' :
                              ($cours->statut === 'annule'   ? 'bg-danger' : 'bg-warning text-dark')) }}">
                            {{ ucfirst(str_replace('_',' ',$cours->statut)) }}
                        </span>
                    </dd>
                </dl>

                <hr>
                <div class="d-grid gap-2">
                    <a href="{{ route('absences.feuille', $cours) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-list-check me-2"></i>Feuille de présence
                    </a>
                    @if(in_array($cours->statut, ['planifie','en_cours']))
                    <a href="{{ route('biometrie.pointage', $cours) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-camera-video me-2"></i>Pointage facial
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('cours.edit', $cours) }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-pencil me-2"></i>Modifier
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Absences --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard-data me-2"></i>Présences ({{ $cours->absences->count() }})</span>
                @php
                    $presents = $cours->absences->where('statut','present')->count();
                    $absents  = $cours->absences->where('statut','absent')->count();
                @endphp
                <div class="d-flex gap-2">
                    <span class="badge badge-present">{{ $presents }} présents</span>
                    <span class="badge badge-absent">{{ $absents }} absents</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Étudiant</th>
                                <th>Statut</th>
                                <th>Facial</th>
                                <th>Justification</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($cours->absences->sortBy('etudiant.user.nom') as $abs)
                        <tr>
                            <td>
                                <div class="fw-semibold small">{{ $abs->etudiant->user->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $abs->etudiant->codePar }}</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-{{ $abs->statut }}">
                                    {{ ucfirst($abs->statut) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($abs->pointage_facial)
                                    <i class="bi bi-patch-check-fill text-success"></i>
                                @else
                                    <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $abs->justification ?? '—' }}</small></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection