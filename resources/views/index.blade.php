@extends('layouts.app')
@section('title', 'Absences')
@section('page-title', 'Gestion des absences')

@section('content')
{{-- Filtres --}}
<div class="card shadow-sm border-0 mb-4">
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
                    <option value="{{ $cl->idClasse ?? $cl->id }}" {{ request('classe')==($cl->idClasse ?? $cl->id) ? 'selected' : '' }}>
                        {{ $cl->nom }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3 d-flex">
                <button type="submit" class="btn btn-primary me-2 w-100">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('absences.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-secondary"><i class="bi bi-clipboard-data me-2"></i>Liste des absences ({{ $absences->total() }})</span>
        <a href="{{ route('absences.rapport') }}" class="btn btn-sm btn-danger shadow-sm">
            <i class="bi bi-file-pdf me-1"></i>Exporter PDF
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Étudiant</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-center">Biométrie</th>
                        <th>Justification</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($absences as $abs)
                @php
                    // Attribution dynamique des couleurs Bootstrap natives
                    $badgeColor = match($abs->statut) {
                        'present'  => 'success',
                        'absent'   => 'danger',
                        'retard'   => 'warning text-dark',
                        'justifie' => 'info text-dark',
                        default    => 'secondary',
                    };
                    // Sécurité d'ID
                    $uid = $abs->idPresence ?? $abs->id;
                @endphp
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold small">{{ $abs->etudiant->user->full_name ?? $abs->etudiant->user->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $abs->etudiant->codePar ?? 'N/A' }}</div>
                    </td>
                    <td><small class="fw-medium">{{ $abs->cours->matiere->nomMatiere ?? 'Matière' }}</small></td>
                    <td><small>{{ $abs->cours->classe->nom ?? 'N/A' }}</small></td>
                    <td><small>{{ $abs->date ? (is_string($abs->date) ? date('d/m/Y', strtotime($abs->date)) : $abs->date->format('d/m/Y')) : '—' }}</small></td>
                    <td>
                        <span class="badge rounded-pill bg-{{ $badgeColor }} px-2 py-1">
                            {{ ucfirst($abs->statut) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($abs->pointage_facial)
                            <span class="text-success" title="Validé par reconnaissance faciale"><i class="bi bi-check-circle-fill"></i></span>
                        @else
                            <span class="text-muted" title="Pointage manuel ou absent"><i class="bi bi-x-circle"></i></span>
                        @endif
                    </td>
                    <td><small class="text-muted">{{ $abs->justification ?? '—' }}</small></td>
                    <td class="text-end pe-3">
                        @if(auth()->user()->isProfesseur() || auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('absences.valider', $abs) }}" class="d-inline-flex gap-1 justify-content-end">
                            @csrf @method('PATCH')
                            <select name="statut" class="form-select form-select-sm" style="width:110px">
                                @foreach(['present','absent','retard','justifie'] as $s)
                                <option value="{{ $s }}" {{ $abs->statut===$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i></button>
                        </form>
                        @elseif(auth()->user()->isEtudiant() && $abs->statut === 'absent')
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#justModal{{ $uid }}">
                            <i class="bi bi-file-text me-1"></i>Justifier
                        </button>
                        @endif
                    </td>
                </tr>

                {{-- Modal justification --}}
                @if(auth()->user()->isEtudiant())
                <div class="modal fade" id="justModal{{ $uid }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('absences.justifier', $abs) }}">
                            @csrf @method('PATCH')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Justifier l'absence</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <textarea name="justification" class="form-control" rows="4"
                                              placeholder="Expliquez la raison de votre absence…" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-warning">Envoyer la justification</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucune absence trouvée.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $absences->withQueryString()->links() }}
    </div>
</div>
@endsection