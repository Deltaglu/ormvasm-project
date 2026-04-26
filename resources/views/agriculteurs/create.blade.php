@extends('layouts.app')

@section('title', 'Nouvel agriculteur — '.config('app.name'))

@section('content')
<x-page-header title="Nouvel agriculteur" subtitle="Créer une fiche exploitant avec identifiant CIN unique.">
    <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="card-body">
        <form method="post" action="{{ route('agriculteurs.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label" for="nom">
                    <i class="bi bi-person me-1 text-secondary"></i>Nom
                </label>
                <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}" required placeholder="Entrez le nom">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="prenom">
                    <i class="bi bi-person me-1 text-secondary"></i>Prénom
                </label>
                <input type="text" name="prenom" id="prenom" class="form-control" value="{{ old('prenom') }}" required placeholder="Entrez le prénom">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cin">
                    <i class="bi bi-card-text me-1 text-secondary"></i>CIN
                </label>
                <input type="text" name="cin" id="cin" class="form-control" value="{{ old('cin') }}" required placeholder="Numéro CIN unique">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone">
                    <i class="bi bi-telephone me-1 text-secondary"></i>Téléphone
                </label>
                <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="Numéro de téléphone">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">
                    <i class="bi bi-envelope me-1 text-secondary"></i>E-mail
                </label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="Adresse email">
            </div>
            <div class="col-12">
                <label class="form-label" for="adresse">
                    <i class="bi bi-geo-alt me-1 text-secondary"></i>Adresse
                </label>
                <textarea name="adresse" id="adresse" class="form-control" rows="2" placeholder="Adresse complète">{{ old('adresse') }}</textarea>
            </div>
            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Enregistrer
                </button>
                <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
