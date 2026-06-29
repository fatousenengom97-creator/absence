@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@push('styles')
<style>
    .col-utilisateur {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 600px;
    }
    .col-header {
        padding: 1rem 1.2rem;
        font-weight: 700;
        font-size: .95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .col-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
    }
    .user-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .7rem 1.2rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }
    .user-row:hover { background: #f8fafc; }
    .user-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .72rem; flex-shrink: 0;
    }
    .user-info { flex: 1; min-width: 0; }
    .user-info .nom { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-info .meta { font-size: .7rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .col-footer {
        padding: .7rem 1.2rem;
        border-top: 1px solid #f1f5f9;
        flex-shrink: 0;
        text-align: center;
    }
    .col-empty { text-align: center; color: #94a3b8; padding: 3rem 1rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="text-muted small">{{ $totalGlobal }} utilisateur(s) au total</div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="row g-3">

    {{-- ===== COLONNE ÉTUDIANTS ===== --}}
    <div class="col-md-6 col-xl-3">
        <div class="col-utilisateur">
            <div class="col-header" style="background:linear-gradient(135deg,#059669,#34d399);color:#fff;">
                <span><i class="bi bi-mortarboard me-2"></i>Étudiants</span>
                <span class="badge bg-white text-success">{{ $etudiants->total() }}</span>
            </div>
            <div class="col-body">
                @forelse($etudiants as $user)
                <div class="user-row">
                    <div class="user-avatar" style="background:#d1fae5;color:#065f46;">
                        {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                    </div>
                    <div class="user-info">
                        <div class="nom">{{ $user->prenom }} {{ $user->nom }}</div>
                        <div class="meta">{{ $user->etudiant->classe->nom ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light" title="Modifier">
                        <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                    </a>
                </div>
                @empty
                <div class="col-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun étudiant</div>
                @endforelse
            </div>
            <div class="col-footer">
                <a href="{{ route('admin.users.etudiants') }}" class="btn btn-sm btn-outline-success w-100">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ===== COLONNE PROFESSEURS ===== --}}
    <div class="col-md-6 col-xl-3">
        <div class="col-utilisateur">
            <div class="col-header" style="background:linear-gradient(135deg,#1a3a5c,#2e86de);color:#fff;">
                <span><i class="bi bi-person-badge me-2"></i>Professeurs</span>
                <span class="badge bg-white text-primary">{{ $professeurs->total() }}</span>
            </div>
            <div class="col-body">
                @forelse($professeurs as $user)
                <div class="user-row">
                    <div class="user-avatar" style="background:#dbeafe;color:#1e40af;">
                        {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                    </div>
                    <div class="user-info">
                        <div class="nom">{{ $user->prenom }} {{ $user->nom }}</div>
                        <div class="meta">{{ $user->professeur->specialite ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light" title="Modifier">
                        <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                    </a>
                </div>
                @empty
                <div class="col-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun professeur</div>
                @endforelse
            </div>
            <div class="col-footer">
                <a href="{{ route('admin.users.professeurs') }}" class="btn btn-sm btn-outline-primary w-100">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ===== COLONNE ADMINISTRATEURS ===== --}}
    <div class="col-md-6 col-xl-3">
        <div class="col-utilisateur">
            <div class="col-header" style="background:linear-gradient(135deg,#dc2626,#f87171);color:#fff;">
                <span><i class="bi bi-shield-lock me-2"></i>Administrateurs</span>
                <span class="badge bg-white text-danger">{{ $administrateurs->total() }}</span>
            </div>
            <div class="col-body">
                @forelse($administrateurs as $user)
                <div class="user-row">
                    <div class="user-avatar" style="background:#fee2e2;color:#991b1b;">
                        {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                    </div>
                    <div class="user-info">
                        <div class="nom">{{ $user->prenom }} {{ $user->nom }}</div>
                        <div class="meta">{{ $user->administrateur->niveauAcces ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light" title="Modifier">
                        <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                    </a>
                </div>
                @empty
                <div class="col-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun administrateur</div>
                @endforelse
            </div>
            <div class="col-footer">
                <a href="{{ route('admin.users.administrateurs') }}" class="btn btn-sm btn-outline-danger w-100">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ===== COLONNE CHEF DE SERVICE ===== --}}
    <div class="col-md-6 col-xl-3">
        <div class="col-utilisateur">
            <div class="col-header" style="background:linear-gradient(135deg,#d97706,#fbbf24);color:#fff;">
                <span><i class="bi bi-briefcase me-2"></i>Chef de Service</span>
                <span class="badge bg-white text-warning">{{ $chefsService->total() }}</span>
            </div>
            <div class="col-body">
                @forelse($chefsService as $user)
                <div class="user-row">
                    <div class="user-avatar" style="background:#fef3c7;color:#92400e;">
                        {{ strtoupper(substr($user->prenom,0,1).substr($user->nom,0,1)) }}
                    </div>
                    <div class="user-info">
                        <div class="nom">{{ $user->prenom }} {{ $user->nom }}</div>
                        <div class="meta">{{ $user->chefService->poste ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light" title="Modifier">
                        <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                    </a>
                </div>
                @empty
                <div class="col-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun chef de service</div>
                @endforelse
            </div>
            <div class="col-footer">
                <a href="{{ route('admin.users.chefs-service') }}" class="btn btn-sm btn-outline-warning w-100">
                    Voir tous <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection