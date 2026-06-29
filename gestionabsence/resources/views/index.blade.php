@extends('layouts.app')
@section('title', 'Absences')
@section('page-title', 'Gestion des absences')

@section('content')
{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="present"  {{ request('statut')=='present'  ? 'selected' : '' }}>Présent</option>
                    <option value="absent"   {{ request('statut')=='absent'   ? 'selected' : '' }}>Absent</option>
                    <option value="retard"   {{ request('statut')=='retard'   ? 'selected' : '' }}>Retard</option>
                    <option value="justifie" {{ request('statut')=='justifie' ? 'selected' : '' }}>Justifié</option>
                </select>
            </div>
            @if(!auth()->user()->isEtudiant())
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Classe</label>
                <select name="classe" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($classes as $cl)
                    <option value="{{ $cl->idClasse }}" {{ request('classe')==$cl->idClasse ? 'selected' : '' }}>
                        {{ $cl->nom }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('absences.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard-data me-2"></i>Liste des absences ({{ $absences->total() }})</span>
        <a href="{{ route('absences.rapport') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-file-pdf me-1"></i>PDF
        </a>
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
                        <th>Statut</th>
                        <th>Pointage facial</th>
                        <th>Justification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absences as $abs)
                <tr>
                    <td>
                        <div class="fw-semibold small">{{ $abs->etudiant->user->full_name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $abs->etudiant->codePar }}</div>
                    </td>
                    <td><small>{{ $abs->cours->matiere->nomMatiere }}</small></td>
                    <td><small>{{ $abs->cours->classe->nom }}</small></td>
                    <td><small>{{ $abs->date->format('d/m/Y') }}</small></td>
                    <td>
                        <span class="badge rounded-pill badge-{{ $abs->statut }}">
                            {{ ucfirst($abs->statut) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($abs->pointage_facial)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-x-circle text-muted"></i>
                        @endif
                    </td>
                    <td><small class="text-muted">{{ $abs->justification ?? '—' }}</small></td>
                    <td>
                        @if(auth()->user()->isProfesseur() || auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('absences.valider', $abs) }}" class="d-flex gap-1">
                            @csrf @method('PATCH')
                            <select name="statut" class="form-select form-select-sm" style="width:120px">
                                @foreach(['present','absent','retard','justifie'] as $s)
                                <option value="{{ $s }}" {{ $abs->statut===$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i></button>
                        </form>
                        @elseif(auth()->user()->isEtudiant() && $abs->statut === 'absent')
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#justModal{{ $abs->idPresence }}">
                            <i class="bi bi-file-text me-1"></i>Justifier
                        </button>
                        @endif
                    </td>
                </tr>

                {{-- Modal justification --}}
                @if(auth()->user()->isEtudiant())
                <div class="modal fade" id="justModal{{ $abs->idPresence }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('absences.justifier', $abs) }}">
                            @csrf @method('PATCH')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Justifier l'absence du {{ $abs->date->format('d/m/Y') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <textarea name="justification" class="form-control" rows="4"
                                              placeholder="Expliquez la raison de votre absence…" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-warning">Envoyer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucune absence trouvée.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $absences->withQueryString()->links() }}
    </div>
</div>
@endsection