@extends('layouts.app')
@section('title', 'Rapports de cours')
@section('page-title', 'Rapports de cours reçus')

@section('content')
<div class="card">
    <div class="card-header" style="background:#0B1F33;color:#fff;">
        <i class="bi bi-file-earmark-text me-2"></i>Rapports transmis après chaque fin de cours
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Professeur</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rapports as $rapport)
                <tr class="{{ !$rapport->lu ? 'table-warning' : '' }}">
                    <td><strong>{{ $rapport->cours->matiere->nomMatiere ?? '—' }}</strong></td>
                    <td>{{ $rapport->cours->classe->nom ?? '—' }}</td>
                    <td>{{ $rapport->cours->professeur->user->prenom ?? '' }} {{ $rapport->cours->professeur->user->nom ?? '' }}</td>
                    <td><small>{{ $rapport->created_at->format('d/m/Y H:i') }}</small></td>
                    <td>
                        @if(!$rapport->lu)
                        <span class="badge bg-warning text-dark">Nouveau</span>
                        @else
                        <span class="badge bg-secondary">Consulté</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('chef.rapports-cours.pdf', $rapport->cours) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>PDF
                            </a>
                            @if(!$rapport->lu)
                            <form method="POST" action="{{ route('chef.rapports-cours.lu', $rapport) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucun rapport reçu pour le moment.
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $rapports->links() }}</div>
</div>
@endsection