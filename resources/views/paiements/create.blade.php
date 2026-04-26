@extends('layouts.app')

@section('title', 'Nouveau paiement')

@section('content')
<x-page-header title="Nouveau paiement" subtitle="Une quittance PDF est générée automatiquement après enregistrement.">
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="card-body">
        <form method="post" action="{{ route('paiements.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label" for="reference">Référence (auto-générée si vide)</label>
                <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}" placeholder="Ex: PAI2025010001">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="titre_recette_id">Titre de recette</label>
                <select name="titre_recette_id" id="titre_recette_id" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($titresRecettes as $titre)
                        <option value="{{ $titre->id }}" @selected(old('titre_recette_id') == $titre->id)>{{ $titre->numero }} — {{ $titre->agriculteur?->prenom }} {{ $titre->agriculteur?->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="montant">Montant (DH)</label>
                <input type="number" step="0.01" min="0.01" name="montant" id="montant" class="form-control @error('montant') is-invalid @enderror" value="{{ old('montant') }}" required>
                @error('montant')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_paiement">Date</label>
                <input type="date" name="date_paiement" id="date_paiement" class="form-control" value="{{ old('date_paiement', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="type_paiement">Type de paiement</label>
                <select name="type_paiement" id="type_paiement" class="form-select" required>
                    @foreach(['ESPECES' => 'Espèces', 'CHEQUE' => 'Chèque', 'VIREMENT' => 'Virement'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('type_paiement') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="statut">Statut</label>
                <input type="text" name="statut" id="statut" class="form-control" value="{{ old('statut', 'VALIDE') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="numero_cheque">Numéro de chèque</label>
                <input type="text" name="numero_cheque" id="numero_cheque" class="form-control" value="{{ old('numero_cheque') }}" placeholder="Si applicable">
            </div>
            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
                <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection