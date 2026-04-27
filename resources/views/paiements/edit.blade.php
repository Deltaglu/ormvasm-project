@extends('layouts.app')

@section('title', 'Modifier paiement — '.config('app.name'))

@section('content')
<x-page-header title="Modifier le paiement" subtitle="Réf. {{ $paiement->reference }}">
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="ormsa-surface-header">
        <i class="bi bi-pencil-square"></i> Modification du paiement
    </div>

    <form method="post" action="{{ route('paiements.update', $paiement) }}" class="row g-3 p-0">
        @csrf @method('PUT')

        <div class="col-md-6">
            <label class="form-label" for="reference">Référence <span class="text-danger">*</span></label>
            <input type="text" name="reference" id="reference"
                   class="form-control @error('reference') is-invalid @enderror"
                   value="{{ old('reference', $paiement->reference) }}" required>
            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="titre_recette_id">Titre de recette <span class="text-danger">*</span></label>
            <select name="titre_recette_id" id="titre_recette_id" class="form-select @error('titre_recette_id') is-invalid @enderror" required>
                @foreach($titresRecettes as $titre)
                    <option value="{{ $titre->id }}" @selected(old('titre_recette_id', $paiement->titre_recette_id) == $titre->id)>
                        {{ $titre->numero }} — {{ $titre->agriculteur?->type === 'society' ? $titre->agriculteur?->nom : ($titre->agriculteur?->prenom . ' ' . $titre->agriculteur?->nom) }}
                    </option>
                @endforeach
            </select>
            @error('titre_recette_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="montant">Montant (DH) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" name="montant" id="montant"
                   class="form-control fw-semibold text-primary @error('montant') is-invalid @enderror"
                   value="{{ old('montant', $paiement->montant) }}" required>
            @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="date_paiement">Date <span class="text-danger">*</span></label>
            <input type="date" name="date_paiement" id="date_paiement"
                   class="form-control @error('date_paiement') is-invalid @enderror"
                   value="{{ old('date_paiement', $paiement->date_paiement->format('Y-m-d')) }}" required>
            @error('date_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="type_paiement">Type de paiement <span class="text-danger">*</span></label>
            <select name="type_paiement" id="type_paiement" class="form-select @error('type_paiement') is-invalid @enderror" required>
                @foreach(['ESPECES' => 'Espèces', 'CHEQUE' => 'Chèque', 'VIREMENT' => 'Virement'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('type_paiement', $paiement->type_paiement) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="statut">Statut</label>
            <input type="text" name="statut" id="statut"
                   class="form-control @error('statut') is-invalid @enderror"
                   value="{{ old('statut', $paiement->statut) }}" required>
            @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="numero_cheque">Numéro de chèque / virement</label>
            <input type="text" name="numero_cheque" id="numero_cheque"
                   class="form-control @error('numero_cheque') is-invalid @enderror"
                   value="{{ old('numero_cheque', $paiement->numero_cheque) }}">
            @error('numero_cheque')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex gap-2 pt-3 mt-4" style="border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Mettre à jour
            </button>
            <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
