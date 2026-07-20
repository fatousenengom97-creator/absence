@extends('layouts.app')
@section('title', 'EDT — ' . $classe->nom)
@section('page-title', 'Emploi du temps — ' . $classe->nom)

@push('styles')
<style>
    .grille-edt { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .grille-edt th { background: #0B1F33; color: #fff; text-align: center; font-size: .82rem; padding: 10px 6px; border: 1px solid #1e3a5c; }
    .grille-edt td { border: 1px solid #e5e7eb; padding: 3px; vertical-align: top; height: 60px; background: #fafafa; }
    .heure-col { width: 70px !important; background: #f1f5f9 !important; text-align: center; font-size: .75rem; color: #64748b; font-weight: 700; vertical-align: middle !important; }
    .bloc-cours {
        border-radius: 8px;
        padding: 6px 8px;
        font-size: .72rem;
        color: #fff;
        min-height: 54px;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .bloc-cours .mat { font-weight: 700; font-size: .78rem; line-height: 1.2; }
    .bloc-cours .info { opacity: .88; font-size: .68rem; line-height: 1.3; }
    .bloc-cours .bas { display: flex; justify-content: space-between; align-items: center; margin-top: 3px; gap: 4px; }
    .badge-type { font-size: .6rem; padding: 1px 5px; border-radius: 4px; background: rgba(255,255,255,.25); color: #fff; font-weight: 700; }
    .btn-suppr, .btn-modif { font-size: .6rem; padding: 1px 4px; border: none; border-radius: 4px; background: rgba(0,0,0,.25); color: #fff; cursor: pointer; }
    .btn-suppr:hover, .btn-modif:hover { background: rgba(0,0,0,.45); }
    #form-edt.mode-edition { border: 2px solid #f59e0b; border-radius: 8px; padding: 8px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('chef.edt.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour aux classes
    </a>
    <div>
        <span class="badge bg-primary me-1">{{ $classe->filiere->nomFiliere ?? '' }}</span>
        <span class="badge bg-info">{{ $classe->niveau->nom ?? '' }}</span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    @foreach($errors->all() as $e)
    <div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>
    @endforeach
</div>
@endif

{{-- Formulaire ajout/modification créneau --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0B1F33;color:#fff;">
        <span id="form-titre"><i class="bi bi-plus-circle me-2"></i>Ajouter un créneau</span>
        <a href="#" id="btn-annuler-edition" class="btn btn-sm btn-outline-light" style="display:none;" onclick="annulerEdition(); return false;">
            <i class="bi bi-x-circle me-1"></i>Annuler la modification
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('chef.edt.store', $classe) }}" id="form-edt">
            @csrf
            <input type="hidden" name="_method" id="method-field" value="">

            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="champ-date" class="form-control"
                           min="{{ now()->startOfWeek()->format('Y-m-d') }}"
                           value="{{ old('date', $debutSemaine->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Début <span class="text-danger">*</span></label>
                    <select name="heureDebut" id="champ-heureDebut" class="form-select" required>
                        <option value="">--</option>
                        @foreach($heures as $h)
                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                        <option value="{{ $val }}" {{ old('heureDebut') == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
                    <select name="heureFin" id="champ-heureFin" class="form-select" required>
                        <option value="">--</option>
                        @foreach($heures as $h)
                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                        <option value="{{ $val }}" {{ old('heureFin') == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                        <option value="19:00" {{ old('heureFin') == '19:00' ? 'selected' : '' }}>19:00</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="idMatiere" id="champ-idMatiere" class="form-select" required>
                        <option value="">-- Matière --</option>
                        @foreach($matieres as $m)
                        <option value="{{ $m->idMatiere }}" {{ old('idMatiere') == $m->idMatiere ? 'selected' : '' }}>
                            {{ $m->nomMatiere }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Type</label>
                    <select name="typeCours" id="champ-typeCours" class="form-select">
                        <option value="CM">CM</option>
                        <option value="TD">TD</option>
                        <option value="TP">TP</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Professeur <span class="text-danger">*</span></label>
                    <select name="professeur_id" id="champ-professeur_id" class="form-select" required>
                        <option value="">-- Professeur --</option>
                        @foreach($professeurs as $p)
                        <option value="{{ $p->id }}" {{ old('professeur_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->user->prenom }} {{ $p->user->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
                    <select name="idSalle" id="champ-idSalle" class="form-select" required>
                        <option value="">-- Salle --</option>
                        @foreach($salles as $s)
                        <option value="{{ $s->idSalle }}" {{ old('idSalle') == $s->idSalle ? 'selected' : '' }}>
                            {{ $s->nom }} ({{ $s->capacite }} pl.)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Couleur</label>
                    <input type="color" name="couleur" id="champ-couleur" class="form-control form-control-color w-100"
                           value="{{ old('couleur', '#3B82F6') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" id="btn-submit-edt" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i>Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Navigation semaine --}}
@php
    $semainePrec = $debutSemaine->copy()->subWeek()->format('Y-m-d');
    $semaineSuiv = $debutSemaine->copy()->addWeek()->format('Y-m-d');
@endphp

{{-- Grille EDT --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0B1F33;color:#fff;">
        @if($semainePassee)
        <span class="btn btn-sm btn-outline-light disabled" style="opacity:.3;">
            <i class="bi bi-chevron-left"></i> Précédente
        </span>
        @else
        <a href="?semaine={{ $semainePrec }}" class="btn btn-sm btn-outline-light">
            <i class="bi bi-chevron-left"></i> Précédente
        </a>
        @endif

        <span>
            <i class="bi bi-calendar-week me-2"></i>
            {{ $classe->nom }} — Semaine du {{ $debutSemaine->format('d/m/Y') }} au {{ $finSemaine->format('d/m/Y') }}
        </span>

        <a href="?semaine={{ $semaineSuiv }}" class="btn btn-sm btn-outline-light">
            Suivante <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="grille-edt">
                <thead>
                    <tr>
                        <th style="width:70px;">Heure</th>
                        @foreach($jours as $i => $jour)
                        @php $dateJour = $debutSemaine->copy()->addDays($i); @endphp
                        <th style="min-width:150px;">
                            {{ $jour }}<br><small style="font-weight:400;opacity:.8;">{{ $dateJour->format('d/m') }}</small>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($heures as $h)
                @php $hStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                <tr>
                    <td class="heure-col">{{ $hStr }}</td>
                    @foreach($jours as $i => $jour)
                    @php
                        $dateJour = $debutSemaine->copy()->addDays($i)->format('Y-m-d');
                        $creneauxHeure = collect();
                        if (isset($edt[$dateJour])) {
                            $creneauxHeure = $edt[$dateJour]->filter(function($c) use ($hStr) {
                                return substr($c->heureDebut, 0, 5) === $hStr;
                            });
                        }
                    @endphp
                    <td>
                        @foreach($creneauxHeure as $c)
                        <div class="bloc-cours" style="background:{{ $c->couleur ?? '#3B82F6' }};">
                            <div class="mat">{{ $c->matiere->nomMatiere ?? '—' }}</div>
                            <div class="info">
                                🕐 {{ substr($c->heureDebut,0,5) }} — {{ substr($c->heureFin,0,5) }}<br>
                                👤 {{ $c->professeur->user->prenom ?? '' }} {{ $c->professeur->user->nom ?? '' }}<br>
                                📍 {{ $c->salle->nom ?? '—' }}
                            </div>
                            <div class="bas">
                                <span class="badge-type">{{ $c->typeCours }}</span>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn-modif"
                                        onclick="chargerPourEdition({
                                            idEDT: {{ $c->idEDT }},
                                            date: '{{ \Carbon\Carbon::parse($c->date)->format('Y-m-d') }}',
                                            heureDebut: '{{ substr($c->heureDebut,0,5) }}',
                                            heureFin: '{{ substr($c->heureFin,0,5) }}',
                                            idMatiere: '{{ $c->idMatiere }}',
                                            professeur_id: '{{ $c->professeur_id }}',
                                            idSalle: '{{ $c->idSalle }}',
                                            typeCours: '{{ $c->typeCours }}',
                                            couleur: '{{ $c->couleur ?? '#3B82F6' }}'
                                        })">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST"
                                          action="{{ route('chef.edt.destroy', $c->idEDT) }}"
                                          onsubmit="return confirm('Supprimer ce créneau ?')"
                                          style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button class="btn-suppr">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </td>
                    @endforeach
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const urlStore = "{{ route('chef.edt.store', $classe) }}";

function chargerPourEdition(creneau) {
    document.getElementById('champ-date').value = creneau.date;
    document.getElementById('champ-heureDebut').value = creneau.heureDebut;
    document.getElementById('champ-heureFin').value = creneau.heureFin;
    document.getElementById('champ-idMatiere').value = creneau.idMatiere;
    document.getElementById('champ-professeur_id').value = creneau.professeur_id;
    document.getElementById('champ-idSalle').value = creneau.idSalle;
    document.getElementById('champ-typeCours').value = creneau.typeCours;
    document.getElementById('champ-couleur').value = creneau.couleur;

    const form = document.getElementById('form-edt');
    form.action = urlStore.replace(/\/[^\/]*$/, '/emploi-du-temps/creneau/' + creneau.idEDT);
    // Construction propre de l'URL de mise à jour
    form.action = "{{ url('chef/emploi-du-temps/creneau') }}/" + creneau.idEDT;
    document.getElementById('method-field').value = 'PUT';

    document.getElementById('form-titre').innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier le créneau';
    document.getElementById('btn-submit-edt').innerHTML = '<i class="bi bi-save me-1"></i>Enregistrer les modifications';
    document.getElementById('btn-annuler-edition').style.display = 'inline-block';
    form.classList.add('mode-edition');

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function annulerEdition() {
    const form = document.getElementById('form-edt');
    form.reset();
    form.action = urlStore;
    document.getElementById('method-field').value = '';

    document.getElementById('form-titre').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Ajouter un créneau';
    document.getElementById('btn-submit-edt').innerHTML = '<i class="bi bi-save me-1"></i>Ajouter';
    document.getElementById('btn-annuler-edition').style.display = 'none';
    form.classList.remove('mode-edition');
}
</script>
@endsection