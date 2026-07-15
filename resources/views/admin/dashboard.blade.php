@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Tableau de bord — Administrateur')

@section('content')
<div class="row g-4">
    <div class="col-md-3">
        <div class="card p-3">
            <h5>{{ $stats['etudiants'] }}</h5>
            <small class="text-muted">Étudiants</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5>{{ $stats['professeurs'] }}</h5>
            <small class="text-muted">Professeurs</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5>{{ $stats['classes'] }}</h5>
            <small class="text-muted">Classes</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5>{{ $stats['absences_today'] }}</h5>
            <small class="text-muted">Absences aujourd'hui</small>
        </div>
    </div>
</div>
@endsection