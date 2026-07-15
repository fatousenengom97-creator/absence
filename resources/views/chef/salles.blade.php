@extends('layouts.app')
@section('title', 'Disponibilité des salles')
@section('page-title', 'Disponibilité des salles')

@push('styles')
<style>
    .salle-libre { background: #d1fae5; border-left: 4px solid #10b981; }
    .salle-occupee { background: #fee2e2; border-left: 4px solid #ef4444; }
    .salle-card { border-radius: 10px; padding: 1rem; margin-bottom: .5rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="text-muted mb-0">
            <i class="bi bi-calendar-day me-2"></i>
            Aujourd'hui : <strong>{{ $jourActuel }}</strong>
            — {{ now()->format('d/m/Y') }}
        </h6>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success px-3 py-2"><i class="bi bi-circle-fill me-1"></i>Libre</span>
        <span class="badge bg-danger px-3 py-2"><i class="bi bi-circle-fill me-1"></i>Occupée</span>
    </div>
</div>

{{-- Filtre par heure --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3">
            <label class="fw-semibold mb-0">Voir à :</label>
            <select id="filtreHeure" class="form-select form-select-sm" style="width:auto;"
                    onchange="filtrerHeure(this.value)">
                <option value="">-- Toute la journée --</option>
                @for($h = 8; $h <= 19; $h++)
                <option value="{{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00"
                    {{ now()->format('H') == $h ? 'selected':'' }}>
                    {{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00
                </option>
                @endfor
            </select>
            <small class="text-muted">Filtrer les salles occupées à une heure précise</small>
        </div>
    </div>
</div>

<div class="row g-4">
    @foreach($salles as $salle)
    @php
        $creneauxSalle = $edtJour[$salle->idSalle] ?? collect();
        $heureActuelle = now()->format('H:i');
        $occupeeNow    = $creneauxSalle->filter(fn($c) =>
            $c->heureDebut <= $heureActuelle && $c->heureFin > $heureActuelle
        )->isNotEmpty();
    @endphp
    <div class="col-md-6 col-lg-4 salle-item"
         data-creneaux="{{ $creneauxSalle->pluck('heureDebut')->implode(',') }}"
         data-fins="{{ $creneauxSalle->pluck('heureFin')->implode(',') }}">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center
                {{ $occupeeNow ? 'bg-danger text-white' : 'bg-success text-white' }}">
                <span><i class="bi bi-grid me-2"></i>{{ $salle->nom }}</span>
                <span class="badge bg-white {{ $occupeeNow ? 'text-danger' : 'text-success' }}">
                    {{ $occupeeNow ? 'Occupée' : 'Libre' }}
                </span>
            </div>
            <div class="card-body">
                <div class="text-muted small mb-3">
                    <i class="bi bi-people me-1"></i>Capacité : {{ $salle->capacite }} places
                </div>

                @if($creneauxSalle->isEmpty())
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                    Libre toute la journée
                </div>
                @else
                <div class="fw-semibold small mb-2">Créneaux du jour :</div>
                @foreach($creneauxSalle->sortBy('heureDebut') as $c)
                @php
                    $estActuel = $c->heureDebut <= $heureActuelle && $c->heureFin > $heureActuelle;
                @endphp
                <div class="salle-card {{ $estActuel ? 'salle-occupee' : 'salle-libre' }}">
                    <div class="fw-semibold small">{{ $c->heureDebut }} — {{ $c->heureFin }}</div>
                    <div class="small">📚 {{ $c->matiere->nomMatiere ?? '—' }}</div>
                    <div class="small">👤 {{ $c->professeur->user->prenom ?? '' }} {{ $c->professeur->user->nom ?? '' }}</div>
                    <div class="small">🎓 {{ $c->classe->nom ?? '—' }}</div>
                    <span class="badge bg-dark">{{ $c->typeCours ?? '' }}</span>
                    @if($estActuel)
                    <span class="badge bg-danger ms-1">En cours</span>
                    @endif
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function filtrerHeure(heure) {
    document.querySelectorAll('.salle-item').forEach(item => {
        if (!heure) {
            item.style.display = 'block';
            return;
        }

        const debuts = item.dataset.creneaux ? item.dataset.creneaux.split(',') : [];
        const fins   = item.dataset.fins ? item.dataset.fins.split(',') : [];
        let occupee  = false;

        for (let i = 0; i < debuts.length; i++) {
            if (debuts[i] <= heure && fins[i] > heure) {
                occupee = true;
                break;
            }
        }

        // Mettre à jour le badge
        const header = item.querySelector('.card-header');
        const badge  = item.querySelector('.card-header .badge');
        if (occupee) {
            header.classList.remove('bg-success');
            header.classList.add('bg-danger');
            badge.classList.remove('text-success');
            badge.classList.add('text-danger');
            badge.textContent = 'Occupée';
        } else {
            header.classList.remove('bg-danger');
            header.classList.add('bg-success');
            badge.classList.remove('text-danger');
            badge.classList.add('text-success');
            badge.textContent = 'Libre';
        }

        item.style.display = 'block';
    });
}

// Filtrer automatiquement à l'heure actuelle au chargement
window.onload = function() {
    const select = document.getElementById('filtreHeure');
    if (select.value) filtrerHeure(select.value);
};
</script>
@endpush