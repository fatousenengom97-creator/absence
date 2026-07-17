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
    .bloc-cours .bas { display: flex; justify-content: space-between; align-items: center; margin-top: 3px; }
    .badge-type { font-size: .6rem; padding: 1px 5px; border-radius: 4px; background: rgba(255,255,255,.25); color: #fff; font-weight: 700; }
    .btn-suppr { font-size: .6rem; padding: 1px 4px; border: none; border-radius: 4px; background: rgba(0,0,0,.25); color: #fff; cursor: pointer; }
    .btn-suppr:hover { background: rgba(0,0,0,.45); }
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

{{-- Formulaire ajout créneau --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-plus-circle me-2"></i>Ajouter un créneau
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('chef.edt.store', $classe) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Jour <span class="text-danger">*</span></label>
                    <select name="jour" class="form-select" required>
                        <option value="">-- Jour --</option>
                        @foreach($jours as $j)
                        <option value="{{ $j }}" {{ old('jour') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Début <span class="text-danger">*</span></label>
                    <select name="heureDebut" class="form-select" required>
                        <option value="">--</option>
                        @foreach($heures as $h)
                        @php $val = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                        <option value="{{ $val }}" {{ old('heureDebut') == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
                    <select name="heureFin" class="form-select" required>
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
                    <select name="idMatiere" class="form-select" required>
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
                    <select name="typeCours" class="form-select">
                        <option value="CM">CM</option>
                        <option value="TD">TD</option>
                        <option value="TP">TP</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Professeur <span class="text-danger">*</span></label>
                    <select name="professeur_id" class="form-select" required>
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
                    <select name="idSalle" class="form-select" required>
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
                    <input type="color" name="couleur" class="form-control form-control-color w-100"
                           value="{{ old('couleur', '#3B82F6') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i>Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Grille EDT --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0B1F33;color:#fff;">
        <span><i class="bi bi-calendar-week me-2"></i>{{ $classe->nom }}</span>
        <small style="color:#00D9C0;">Lundi → Samedi • 08h00 → 19h00</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="grille-edt">
                <thead>
                    <tr>
                        <th style="width:70px;">Heure</th>
                        @foreach($jours as $jour)
                        <th style="min-width:150px;">{{ $jour }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($heures as $h)
                @php $hStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                <tr>
                    <td class="heure-col">{{ $hStr }}</td>
                    @foreach($jours as $jour)
                    @php
                        // Filtrer les créneaux qui commencent exactement à cette heure
                        $creneauxHeure = collect();
                        if (isset($edt[$jour])) {
                            $creneauxHeure = $edt[$jour]->filter(function($c) use ($hStr) {
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
@endsection