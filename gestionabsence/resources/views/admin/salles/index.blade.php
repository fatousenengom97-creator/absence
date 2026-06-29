@extends('layouts.app')
@section('title', 'Salles')
@section('page-title', 'Gestion des salles')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle salle
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.salles.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom de la salle <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom') }}" placeholder="Ex: Salle A101" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Capacité</label>
                        <input type="number" name="capacite" class="form-control" value="{{ old('capacite', 0) }}" min="0">
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
                <i class="bi bi-grid me-2"></i>Toutes les salles ({{ $salles->total() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Capacité</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($salles as $salle)
                        <tr>
                            <td><strong>{{ $salle->nom }}</strong></td>
                            <td><span class="badge bg-info">{{ $salle->capacite }} places</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-5">
                            <i class="bi bi-grid fs-2 d-block mb-2"></i>Aucune salle trouvée.
                        </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $salles->links() }}</div>
        </div>
    </div>
</div>
@endsection