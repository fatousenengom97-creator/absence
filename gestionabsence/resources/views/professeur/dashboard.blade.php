@extends('layouts.app')
@section('title', 'Dashboard Professeur')
@section('page-title', 'Mon tableau de bord')

@section('content')
<div class="card p-4">
    <h5>Bienvenue {{ auth()->user()->prenom }} !</h5>
    <p class="text-muted">Votre espace professeur est en construction.</p>
</div>
@endsection