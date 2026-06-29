@extends('layouts.app')
@section('title', 'Matières')
@section('page-title', 'Gestion des matières')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle matière
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.matieres.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom de la matière <span class="text-danger">*</span></label>
                        <input type="text" name="nomMatiere" class="form-control @error('nomMatiere') is-invalid @enderror"
                               value="{{ old('nomMatiere') }}" required>
                        @error('nomMatiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code UE</label>
                        <input type="text" name="codeUE" class="form-control" value="{{ old('codeUE') }}" placeholder="Ex: INF101">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Coefficient</label>
                        <input type="number" step="0.5" name="coefficient" class="form-control" value="{{ old('coefficient', 1) }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-2"></i>Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-text me-2"></i>Toutes les matières ({{ $matieres->total() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Code UE</th>
                                <th>Coefficient</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($matieres as $matiere)
                        <tr>
                            <td><strong>{{ $matiere->nomMatiere }}</strong></td>
                            <td><code>{{ $matiere->codeUE ?? '—' }}</code></td>
                            <td><span class="badge bg-info">{{ $matiere->coefficient }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>Aucune matière trouvée.
                        </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $matieres->links() }}</div>
        </div>
    </div>
</div>
@endsection