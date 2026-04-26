@extends('layouts.app')

@section('title', 'Nouvel agriculteur — '.config('app.name'))

@section('content')
<x-page-header title="Nouvel agriculteur" subtitle="Créer une fiche exploitant avec identifiant CIN unique.">
    <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="ormsa-surface-header">
        <i class="bi bi-person-plus"></i>
        Informations de l'agriculteur
    </div>

    <form method="post" action="{{ route('agriculteurs.store') }}" class="row g-3 p-0">
        @csrf

        <div class="col-md-6">
            <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="nom"
                   class="form-control @error('nom') is-invalid @enderror"
                   value="{{ old('nom') }}" required placeholder="Nom de famille">
            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
            <input type="text" name="prenom" id="prenom"
                   class="form-control @error('prenom') is-invalid @enderror"
                   value="{{ old('prenom') }}" required placeholder="Prénom">
            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="cin">CIN <span class="text-danger">*</span></label>
            <input type="text" name="cin" id="cin"
                   class="form-control @error('cin') is-invalid @enderror"
                   value="{{ old('cin') }}" required placeholder="Numéro CIN unique">
            @error('cin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone"
                   class="form-control @error('telephone') is-invalid @enderror"
                   value="{{ old('telephone') }}" placeholder="0600 000 000">
            @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="exemple@domaine.ma">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label" for="adresse">Adresse</label>
            <textarea name="adresse" id="adresse"
                      class="form-control @error('adresse') is-invalid @enderror"
                      rows="2" placeholder="Adresse complète">{{ old('adresse') }}</textarea>
            @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex gap-2 pt-2" style="border-top:1px solid var(--border); margin-top:.5rem;">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Enregistrer
            </button>
            <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
