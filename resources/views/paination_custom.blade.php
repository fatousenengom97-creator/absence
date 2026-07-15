@if ($paginator->hasPages())
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 py-2">

    {{-- Info résultats --}}
    <div class="text-muted small">
        Affichage de <strong>{{ $paginator->firstItem() }}</strong> à <strong>{{ $paginator->lastItem() }}</strong>
        sur <strong>{{ $paginator->total() }}</strong> résultats
    </div>

    <div class="d-flex align-items-center gap-2">

        {{-- Bouton Précédent --}}
        @if ($paginator->onFirstPage())
            <button class="btn btn-sm btn-outline-secondary" disabled>
                <i class="bi bi-chevron-left"></i> Précédent
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-chevron-left"></i> Précédent
            </a>
        @endif

        {{-- Liste déroulante "Aller à la page" --}}
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Page</span>
            <select class="form-select form-select-sm" style="width:auto;" onchange="window.location.href=this.value">
                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    <option value="{{ $paginator->url($i) }}" {{ $paginator->currentPage() == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
            <span class="text-muted small">sur {{ $paginator->lastPage() }}</span>
        </div>

        {{-- Bouton Suivant --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">
                Suivant <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <button class="btn btn-sm btn-outline-secondary" disabled>
                Suivant <i class="bi bi-chevron-right"></i>
            </button>
        @endif

    </div>
</div>
@endif