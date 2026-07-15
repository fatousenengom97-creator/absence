@extends('layouts.app')
@section('title', 'Feuille de présence')
@section('page-title', 'Feuille de présence')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>{{ $cours->matiere->nomMatiere }}</strong>
            <span class="text-muted ms-2">{{ $cours->classe->nom }}</span>
            <span class="badge bg-secondary ms-2">{{ $cours->heureDebut->format('d/m/Y H:i') }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('biometrie.pointage', $cours) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-camera-video me-1"></i>Pointage facial
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('absences.enregistrer', $cours) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Étudiant</th>
                            <th>Code</th>
                            <th>Statut</th>
                            <th>Pointage facial</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($cours->absences->sortBy('etudiant.user.nom') as $i => $abs)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $abs->etudiant->user->full_name }}</div>
                        </td>
                        <td><code class="small">{{ $abs->etudiant->codePar }}</code></td>
                        <td>
                            <select name="presences[{{ $abs->etudiant->id }}]"
                                    class="form-select form-select-sm statut-select"
                                    style="min-width:130px;"
                                    data-id="{{ $abs->etudiant->id }}">
                                @foreach(['present'=>'Présent','absent'=>'Absent','retard'=>'Retard','justifie'=>'Justifié'] as $val => $label)
                                <option value="{{ $val }}" {{ $abs->statut === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            @if($abs->pointage_facial)
                                <i class="bi bi-patch-check-fill text-success fs-5" title="Pointé par caméra"></i>
                            @else
                                <i class="bi bi-dash-circle text-muted" title="Non pointé"></i>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Sélection rapide --}}
            <div class="d-flex gap-2 flex-wrap mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" id="btnTousPresents">
                    <i class="bi bi-check-all me-1"></i>Tous présents
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnTousAbsents">
                    <i class="bi bi-x-circle me-1"></i>Tous absents
                </button>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Enregistrer la présence
                </button>
                <a href="{{ route('cours.show', $cours) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnTousPresents').addEventListener('click', () => {
    document.querySelectorAll('.statut-select').forEach(s => s.value = 'present');
});
document.getElementById('btnTousAbsents').addEventListener('click', () => {
    document.querySelectorAll('.statut-select').forEach(s => s.value = 'absent');
});
</script>
@endpush