@extends('layouts.app') {{-- Adapte si ton layout s'appelle différemment, ex: layouts.chef --}}

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Fil d'Ariane / En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-muted fw-normal">
            Emploi du temps — {{ $classe->nom }}
        </h5>
        <div>
            <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">Chef service</span>
            <span class="ms-2 fw-bold text-dark"><i class="bi bi-person-circle"></i> {{ Auth::user()->name }}</span>
        </div>
    </div>

    {{-- Bouton Retour --}}
    <div class="mb-4">
        <a href="{{ route('chef.edt.index') }}" class="btn btn-light border text-secondary bg-white">
            <i class="bi bi-arrow-left"></i> Retour aux classes
        </a>
    </div>

    {{-- ========================================================================= --}}
    {{-- ⚠️ ZONE DE NOTIFICATION DU BLOCAGE DES CONFLITS D'HORAIRE                 --}}
    {{-- ========================================================================= --}}
    @if ($errors->has('erreur_conflit'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                <div>
                    <strong>Erreur de planification :</strong> {{ $errors->first('erreur_conflit') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    {{-- ========================================================================= --}}
    {{-- ➕ FORMULAIRE HORIZONTAL : AJOUTER UN CRÉNEAU                             --}}
    {{-- ========================================================================= --}}
    <div class="card shadow-sm mb-4 border-0 text-white" style="background-color: #0f172a;">
        <div class="card-header border-0 bg-transparent pt-3 pb-1">
            <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-1"></i> Ajouter un créneau à l'emploi du temps</h6>
        </div>
        <div class="card-body pt-2 pb-3">
            <form action="{{ route('emploi_du_temps.store') }}" method="POST">
                @csrf
                {{-- ID Masqué envoyé au contrôleur --}}
                <input type="hidden" name="idClasse" value="{{ $classe->id }}">

                <div class="row g-3 align-items-end">
                    {{-- Jour (anciennement Date, adapté pour ta liste de Jours) --}}
                    <div class="col-md-2.5 col-sm-6">
                        <label class="form-label small fw-bold text-light mb-1">Jour <span class="text-danger">*</span></label>
                        <select name="date" class="form-select form-select-sm" required>
                            <option value="">-- Jour --</option>
                            {{-- Tu peux mettre des dates dynamiques ou des valeurs fixes selon ta logique de stockage --}}
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->format('Y-m-d') }}">Lundi</option>
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->addDay(1)->format('Y-m-d') }}">Mardi</option>
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->addDay(2)->format('Y-m-d') }}">Mercredi</option>
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->addDay(3)->format('Y-m-d') }}">Jeudi</option>
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->addDay(4)->format('Y-m-d') }}">Vendredi</option>
                            <option value="{{ Carbon\Carbon::now()->startOfWeek()->addDay(5)->format('Y-m-d') }}">Samedi</option>
                        </select>
                    </div>

                    {{-- Début --}}
                    <div class="col">
                        <label class="form-label small fw-bold text-light mb-1">Début <span class="text-danger">*</span></label>
                        <select name="heure_debut" class="form-select form-select-sm" required>
                            <option value="">--</option>
                            @for ($h = 8; $h <= 18; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Fin --}}
                    <div class="col">
                        <label class="form-label small fw-bold text-light mb-1">Fin <span class="text-danger">*</span></label>
                        <select name="heure_fin" class="form-select form-select-sm" required>
                            <option value="">--</option>
                            @for ($h = 9; $h <= 19; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Matière --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-light mb-1">Matière <span class="text-danger">*</span></label>
                        <select name="idMatiere" class="form-select form-select-sm" required>
                            <option value="">-- Matière --</option>
                            @foreach($matieres as $matiere)
                                <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type --}}
                    <div class="col">
                        <label class="form-label small fw-bold text-light mb-1">Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="CM">CM</option>
                            <option value="TD">TD</option>
                            <option value="TP">TP</option>
                        </select>
                    </div>

                    {{-- Professeur --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-light mb-1">Professeur <span class="text-danger">*</span></label>
                        <select name="professeur_id" class="form-select form-select-sm" required>
                            <option value="">-- Professeur --</option>
                            @foreach($professeurs as $prof)
                                <option value="{{ $prof->id }}">{{ $prof->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Salle --}}
                    <div class="col-md-1.5">
                        <label class="form-label small fw-bold text-light mb-1">Salle <span class="text-danger">*</span></label>
                        <select name="idSalle" class="form-select form-select-sm" required>
                            <option value="">-- Salle --</option>
                            @foreach($salles as $salle)
                                <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Couleur --}}
                    <div class="col">
                        <label class="form-label small fw-bold text-light mb-1">Couleur</label>
                        <input type="color" name="couleur" class="form-control form-control-sm form-control-color w-100" value="#3b82f6" style="height: 31px;">
                    </div>

                    {{-- Bouton Valider --}}
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                            <i class="bi bi-box-arrow-in-down"></i> Ajouter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- 📅 CALENDRIER HEBDOMADAIRE (VUE DU CHEF DE SERVICE)                       --}}
    {{-- ========================================================================= --}}
    <div class="card shadow-sm border-0">
        {{-- Bandeau bleu supérieur --}}
        <div class="card-header border-0 d-flex justify-content-between align-items-center py-2" style="background-color: #0369a1; color: white;">
            <span class="fw-bold"><i class="bi bi-calendar3"></i> {{ $classe->nom }}</span>
            <span class="small fw-semibold">Lundi → Samedi • 08h00 → 19h00</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle mb-0" style="table-layout: fixed;">
                    <thead style="background-color: #0f172a; color: white;">
                        <tr>
                            <th style="width: 80px;" class="py-2 small">Heure</th>
                            <th class="small">Lundi</th>
                            <th class="small">Mardi</th>
                            <th class="small">Mercredi</th>
                            <th class="small">Jeudi</th>
                            <th class="small">Vendredi</th>
                            <th class="small">Samedi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Boucle sur les heures de 08:00 à 18:00 --}}
                        @for ($h = 8; $h <= 18; $h++)
                            @php $heureCourante = sprintf('%02d:00', $h); @endphp
                            <tr>
                                <td class="fw-bold text-secondary small bg-light">{{ $heureCourante }}</td>
                                
                                {{-- Parcours des jours de la semaine (Lundi=1 à Samedi=6) --}}
                                @for ($jourIndex = 1; $jourIndex <= 6; $jourIndex++)
                                    <td>
                                        {{-- Filtrer pour voir s'il y a un cours à ce jour et cette heure précise --}}
                                        @foreach($cours as $c)
                                            @php 
                                                $dateCours = \Carbon\Carbon::parse($c->date);
                                                $jourSemaine = $dateCours->dayOfWeekIso; // Lundi = 1
                                                $heureDebut = \Carbon\Carbon::parse($c->heureDebut)->format('H:i');
                                            @endphp

                                            @if($jourSemaine == $jourIndex && $heureDebut == $heureCourante)
                                                <div class="p-2 rounded text-start text-white position-relative shadow-sm mb-1" 
                                                     style="background-color: {{ $c->couleur ?? '#ec4899' }}; font-size: 11px; line-height: 1.3;">
                                                    
                                                    {{-- Titre de la Matière --}}
                                                    <div class="fw-bold text-truncate">{{ $c->matiere->nom ?? 'N/A' }}</div>
                                                    
                                                    {{-- Horaires --}}
                                                    <div class="small"><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($c->heureDebut)->format('H:i') }} — {{ \Carbon\Carbon::parse($c->heureFin)->format('H:i') }}</div>
                                                    
                                                    {{-- Professeur --}}
                                                    <div class="small"><i class="bi bi-person"></i> {{ $c->professeur->name ?? 'N/A' }}</div>
                                                    
                                                    {{-- Salle --}}
                                                    <div class="small"><i class="bi bi-geo-alt"></i> {{ $c->salle->nom ?? 'N/A' }}</div>
                                                    
                                                    {{-- Badge type de cours (CM/TD/TP) et bouton supprimer --}}
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <span class="badge bg-dark bg-opacity-50 text-uppercase" style="font-size: 9px;">{{ $c->type }}</span>
                                                        
                                                        {{-- Bouton pour supprimer le créneau en direct --}}
                                                        <form action="{{ route('chef.edt.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Supprimer ce créneau ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-white p-0 m-0 lh-1 bg-transparent border-0 opacity-75 hover-opacity-100">
                                                                <i class="bi bi-trash-fill" style="font-size: 11px;"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection