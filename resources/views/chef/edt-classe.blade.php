@extends('layouts.app')
@section('title', 'EDT — ' . $classe->nom)
@section('page-title', 'Emploi du temps — ' . $classe->nom)

@push('styles')
<style>
    .grille-edt { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .grille-edt th { background: #072e55; color: #fff; text-align: center; font-size: .82rem; padding: 10px 6px; border: 1px solid #1e3a5c; }
    .grille-edt td { border: 1px solid #e5e7eb; padding: 3px; vertical-align: top; height: 60px; background: #fafafa; }
    .heure-col { width: 70px !important; background: #f1f5f9 !important; text-align: center; font-size: .75rem; color: #64748b; font-weight: 700; vertical-align: middle !important; }
    .creneau-cours {
        border-radius: 8px;
        padding: 8px;
        font-size: .72rem;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
        box-sizing: border-box;
    }
    .creneau-cours .mat { font-weight: 700; font-size: .78rem; line-height: 1.2; margin-bottom: 4px; }
    .creneau-cours .info { opacity: .88; font-size: .68rem; line-height: 1.4; }
    .creneau-cours .bas { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
    .btn-suppr { font-size: .6rem; padding: 2px 5px; border: none; border-radius: 4px; background: rgba(0,0,0,.25); color: #fff; cursor: pointer; transition: background 0.2s; }
    .btn-suppr:hover { background: rgba(0,0,0,.5); }
    .badge-type { font-size: .6rem; padding: 1px 5px; border-radius: 4px; background: rgba(255,255,255,.25); color: #fff; font-weight: 700; }
    .jour-header { min-width: 140px; }
    tr:hover .heure-col { background: #e2e8f0 !important; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    {{-- On adapte le retour dynamique selon le rôle pour éviter de casser la navigation des étudiants/profs --}}
    @if(auth()->user()->role === 'chef_service')
        <a href="{{ route('chef.edt.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour aux classes
        </a>
    @else
        <span class="text-muted fw-semibold"><i class="bi bi-eye me-1"></i>Mode Consultation</span>
    @endif
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
    @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
</div>
@endif

{{-- 1. SEUL le chef de service a accès au formulaire d'ajout d'un créneau --}}
@if(auth()->user()->role === 'chef_service')
<div class="card mb-4 shadow-sm">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-plus-circle me-2"></i>Ajouter un créneau à l'emploi du temps
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('chef.edt.store', $classe->idClasse) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Jour <span class="text-danger">*</span></label>
                    <select name="jour" class="form-select" required>
                        <option value="">-- Jour --</option>
                        @foreach($jours as $j)
                        <option value="{{ $j }}" {{ old('jour') == $j ? 'selected':'' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Début <span class="text-danger">*</span></label>
                    <select name="heureDebut" class="form-select" required>
                        <option value="">--</option>
                        @foreach($heures as $h)
                        @php $val = str_pad($h,2,'0',STR_PAD_LEFT).':00'; @endphp
                        <option value="{{ $val }}" {{ old('heureDebut') == $val ? 'selected':'' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
                    <select name="heureFin" class="form-select" required>
                        <option value="">--</option>
                        @foreach($heures as $h)
                        @php $val = str_pad($h,2,'0',STR_PAD_LEFT).':00'; @endphp
                        <option value="{{ $val }}" {{ old('heureFin') == $val ? 'selected':'' }}>{{ $val }}</option>
                        @endforeach
                        <option value="19:00" {{ old('heureFin')=='19:00'?'selected':'' }}>19:00</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="idMatiere" class="form-select" required>
                        <option value="">-- Matière --</option>
                        @foreach($matieres as $m)
                        <option value="{{ $m->idMatiere }}" {{ old('idMatiere')==$m->idMatiere?'selected':'' }}>
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Professeur <span class="text-danger">*</span></label>
                    <select name="professeur_id" class="form-select" required>
                        <option value="">-- Professeur --</option>
                        @foreach($professeurs as $p)
                        <option value="{{ $p->id }}" {{ old('professeur_id')==$p->id?'selected':'' }}>
                            {{ $p->user->prenom ?? '' }} {{ $p->user->nom ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
                    <select name="idSalle" class="form-select" required>
                        <option value="">-- Salle --</option>
                        @foreach($salles as $s)
                        <option value="{{ $s->idSalle }}" {{ old('idSalle')==$s->idSalle?'selected':'' }}>
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
@endif

{{-- Grille EDT (Consultable par tout le monde) --}}
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
                        <th class="jour-header">{{ $jour }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @php $occupe = []; @endphp

                @foreach($heures as $h)
                @php $hStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                <tr>
                    <td class="heure-col">{{ $hStr }}</td>
                    
                    @foreach($jours as $jour)
                    @php
                        // Si la cellule est déjà prise par un cours plus long qui a commencé au-dessus
                        if (isset($occupe[$jour][$hStr])) {
                            continue;
                        }

                        $creneau = null;
                        if (isset($edt[$jour])) {
                            // On vérifie s'il y a un cours programmé à cette heure précise
                            $creneau = $edt[$jour]->first(function($c) use ($hStr) {
                                return substr($c->heureDebut, 0, 5) === $hStr;
                            });
                        }
                    @endphp

                    @if($creneau)
                        @php
                            $debutH = (int)substr($creneau->heureDebut, 0, 2);
                            $finH = (int)substr($creneau->heureFin, 0, 2);
                            $duree = $finH - $debutH;
                            
                            // On bloque les cellules des heures suivantes pour ce jour
                            for ($i = 1; $i < $duree; $i++) {
                                $heureSuivante = str_pad($debutH + $i, 2, '0', STR_PAD_LEFT) . ':00';
                                $occupe[$jour][$heureSuivante] = true;
                            }
                        @endphp
                        
                        <td rowspan="{{ $duree }}" style="padding: 4px; background: #fff;">
                            <div class="creneau-cours" style="background:{{ $creneau->couleur ?? '#3B82F6' }}; height: 100%; min-height: {{ ($duree * 60) - 10 }}px;">
                                <div>
                                    <div class="mat">{{ $creneau->matiere->nomMatiere ?? '—' }}</div>
                                    <div class="info">
                                        <i class="bi bi-clock me-1"></i> {{ substr($creneau->heureDebut, 0, 5) }} — {{ substr($creneau->heureFin, 0, 5) }}<br>
                                        <i class="bi bi-person me-1"></i> {{ $creneau->professeur->user->prenom ?? '' }} {{ $creneau->professeur->user->nom ?? '' }}<br>
                                        <i class="bi bi-geo-alt me-1"></i> {{ $creneau->salle->nom ?? '—' }}
                                    </div>
                                </div>
                                <div class="bas">
                                    <span class="badge-type">{{ $creneau->typeCours }}</span>
                                    
                                    {{-- 2. SEUL le chef de service voit le bouton de suppression --}}
                                    @if(auth()->user()->role === 'chef_service')
                                    <form method="POST" action="{{ route('chef.edt.destroy', $creneau->id) }}"
                                          onsubmit="return confirm('Supprimer ce créneau ?')" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-suppr"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    @else
                        <td></td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection