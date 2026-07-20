@extends('layouts.app')
@section('title', $classe->nom)
@section('page-title', 'Étudiants — ' . $classe->nom)

@section('content')
@php $prefix = auth()->user()->role === 'chef_service' ? 'chef' : 'admin'; @endphp

<nav class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route($prefix.'.etudiants-filiere.index') }}">Filières</a></li>
        <li class="breadcrumb-item"><a href="{{ route($prefix.'.etudiants-filiere.classes', $filiere) }}">{{ $filiere->nomFiliere }}</a></li>
        <li class="breadcrumb-item active">{{ $classe->nom }}</li>
    </ol>
</nav>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    @foreach($errors->all() as $e)
    <div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">{{ $classe->nom }} <span class="badge bg-info ms-2">{{ $classe->niveau->nom ?? '—' }}</span></h5>
        <small class="text-muted">{{ $etudiants->total() }} étudiant(s) inscrit(s)</small>
    </div>
    @if($prefix === 'admin')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un étudiant
    </a>
    @endif
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-mortarboard me-2 text-success"></i>Liste des étudiants</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th>Code</th>
                        <th>Email</th>
                        <th>Statut du jour</th>
                        @if($prefix === 'chef')
                        <th>Modifier le statut</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($etudiants as $etudiant)
                @php
                    $absence = $etudiant->derniereAbsence;
                    $colors = ['present'=>'success','absent'=>'danger','retard'=>'warning','justifie'=>'info'];
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;background:#d1fae5;color:#065f46;font-weight:700;flex-shrink:0;font-size:.8rem;">
                                {{ strtoupper(substr($etudiant->user->prenom,0,1).substr($etudiant->user->nom,0,1)) }}
                            </div>
                            <div class="fw-semibold small">{{ $etudiant->user->prenom }} {{ $etudiant->user->nom }}</div>
                        </div>
                    </td>
                    <td><code class="small">{{ $etudiant->codePar }}</code></td>
                    <td><small>{{ $etudiant->user->email }}</small></td>
                    <td>
                        @if($absence)
                        <span class="badge bg-{{ $colors[$absence->statut] ?? 'secondary' }}">
                            {{ ucfirst($absence->statut) }}
                        </span>
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</small>
                        @else
                        <span class="badge bg-secondary">Aucun pointage</span>
                        @endif
                    </td>
                    @if($prefix === 'chef')
                    <td>
                        @if($absence)
                        <form method="POST" action="{{ route('absences.valider', $absence) }}" class="d-flex gap-1" onsubmit="return verifierJustification(this)">
                            @csrf @method('PATCH')
                            <select name="statut" class="form-select form-select-sm statut-select" style="width:120px;" onchange="toggleJustification(this)">
                                <option value="present"  {{ $absence->statut==='present'  ?'selected':'' }}>Présent</option>
                                <option value="absent"   {{ $absence->statut==='absent'   ?'selected':'' }}>Absent</option>
                                <option value="retard"   {{ $absence->statut==='retard'   ?'selected':'' }}>Retard</option>
                                <option value="justifie" {{ $absence->statut==='justifie' ?'selected':'' }}>Justifié</option>
                            </select>
                            <input type="text" name="justification" class="form-control form-control-sm justification-input"
                                   placeholder="Motif de la justification" style="width:170px;"
                                   value="{{ $absence->justification }}">
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-save"></i></button>
                        </form>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    @endif
                    <td>
                        <div class="d-flex gap-1">
                            @if($prefix === 'admin')
                            <a href="{{ route('admin.users.edit', $etudiant->user) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('biometrie.enregistrer', $etudiant) }}" class="btn btn-sm btn-outline-info" title="Biométrie">
                                <i class="bi bi-camera"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>Aucun étudiant dans cette classe.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $etudiants->links() }}</div>
</div>

<script>
function toggleJustification(select) {
    const form = select.closest('form');
    const input = form.querySelector('.justification-input');
    if (select.value === 'justifie') {
        input.setAttribute('required', 'required');
        input.classList.add('border-warning');
    } else {
        input.removeAttribute('required');
        input.classList.remove('border-warning');
    }
}

function verifierJustification(form) {
    const select = form.querySelector('.statut-select');
    const input = form.querySelector('.justification-input');
    if (select.value === 'justifie' && input.value.trim() === '') {
        alert('Veuillez saisir le motif de la justification apportée par l\'étudiant.');
        input.focus();
        return false;
    }
    return true;
}
</script>
@endsection