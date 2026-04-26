@extends('layouts.app')

@section('title', 'Nouveau paiement — '.config('app.name'))

@section('content')
<x-page-header title="Nouveau paiement" subtitle="Une quittance PDF est générée automatiquement après enregistrement.">
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="ormsa-surface-header">
        <i class="bi bi-cash-stack"></i> Enregistrer un encaissement
    </div>

    <form method="post" action="{{ route('paiements.store') }}" class="row g-3 p-0">
        @csrf

        <div class="col-md-6">
            <label class="form-label" for="reference">Référence <span class="text-muted fw-normal">(auto-générée si vide)</span></label>
            <input type="text" name="reference" id="reference"
                   class="form-control @error('reference') is-invalid @enderror"
                   value="{{ old('reference') }}" placeholder="Ex: PAI2025010001">
            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="titre_recette_id">Titre de recette <span class="text-danger">*</span></label>
            <select name="titre_recette_id" id="titre_recette_id" class="form-select searchable-select @error('titre_recette_id') is-invalid @enderror" required>
                <option value="">— Sélectionner —</option>
                @foreach($titresRecettes as $titre)
                    <option value="{{ $titre->id }}" @selected(old('titre_recette_id') == $titre->id)>
                        {{ $titre->numero }} — {{ $titre->agriculteur?->prenom }} {{ $titre->agriculteur?->nom }}
                    </option>
                @endforeach
            </select>
            @error('titre_recette_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="montant">Montant (DH) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" name="montant" id="montant"
                   class="form-control fw-semibold text-primary @error('montant') is-invalid @enderror"
                   value="{{ old('montant') }}" required>
            @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="date_paiement">Date du paiement <span class="text-danger">*</span></label>
            <input type="text" name="date_paiement" id="date_paiement"
                   class="form-control datepicker @error('date_paiement') is-invalid @enderror"
                   value="{{ old('date_paiement', date('Y-m-d')) }}" required>
            @error('date_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="type_paiement">Mode <span class="text-danger">*</span></label>
            <select name="type_paiement" id="type_paiement" class="form-select @error('type_paiement') is-invalid @enderror" required>
                @foreach(['ESPECES' => 'Espèces', 'CHEQUE' => 'Chèque', 'VIREMENT' => 'Virement'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('type_paiement') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="statut">Statut</label>
            <input type="text" name="statut" id="statut"
                   class="form-control @error('statut') is-invalid @enderror"
                   value="{{ old('statut', 'VALIDE') }}" required>
            @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="numero_cheque">Numéro de chèque / virement</label>
            <input type="text" name="numero_cheque" id="numero_cheque"
                   class="form-control @error('numero_cheque') is-invalid @enderror"
                   value="{{ old('numero_cheque') }}" placeholder="Si applicable">
            @error('numero_cheque')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex gap-2 pt-3 mt-4" style="border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Enregistrer le paiement
            </button>
            <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection