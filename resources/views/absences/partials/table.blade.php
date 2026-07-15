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
        @forelse($listeAbsences as $abs)
            @php
                $badgeColor = match($abs->statut) {
                    'present'  => 'success',
                    'absent'   => 'danger',
                    'retard'   => 'warning text-dark',
                    'justifie' => 'info text-dark',
                    default    => 'secondary',
                };
                $uid = $abs->idPresence ?? $abs->id;
            @endphp
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold small">{{ $abs->etudiant->user->full_name ?? $abs->etudiant->user->name ?? 'N/A' }}</div>
                    <div class="text-muted" style="font-size:.72rem;">{{ $abs->etudiant->codePar ?? 'N/A' }}</div>
                </td>
                <td><small class="fw-medium">{{ $abs->cours->matiere->nomMatiere ?? 'Matière' }}</small></td>
                <td><small>{{ $abs->cours->classe->nomClasse ?? $abs->cours->classe->nom ?? 'N/A' }}</small></td>
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
                        <span class="text-muted" title="Pointage manuel"><i class="bi bi-x-circle"></i></span>
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

            {{-- Modal de justification individuel --}}
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
                                <textarea name="justification" class="form-control" rows="4" placeholder="Expliquez la raison de votre absence…" required></textarea>
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