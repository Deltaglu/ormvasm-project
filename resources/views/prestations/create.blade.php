@extends('layouts.app')

@section('title', 'Nouvelle prestation — '.config('app.name'))

@section('content')
<x-page-header title="Nouvelle prestation" subtitle="Définir un code unique, un libellé et un tarif.">
    <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="ormsa-surface-header">
        <i class="bi bi-plus-circle"></i> Création d'une prestation
    </div>

    <form method="post" action="{{ route('prestations.store') }}" class="row g-3 p-0">
        @csrf

        <div class="col-md-4">
            <label class="form-label" for="code">Code <span class="text-muted fw-normal">(auto-généré si vide)</span></label>
            <input type="text" name="code" id="code"
                   class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code') }}" placeholder="Ex: PRE0001">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-8">
            <label class="form-label" for="libelle">Libellé <span class="text-danger">*</span></label>
            <input type="text" name="libelle" id="libelle"
                   class="form-control @error('libelle') is-invalid @enderror"
                   value="{{ old('libelle') }}" required>
            @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="tarif">Tarif (DH) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="tarif" id="tarif"
                   class="form-control fw-semibold text-primary @error('tarif') is-invalid @enderror"
                   value="{{ old('tarif') }}" required>
            @error('tarif')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="unite">Unité</label>
            <input type="text" name="unite" id="unite"
                   class="form-control @error('unite') is-invalid @enderror"
                   value="{{ old('unite') }}" placeholder="Ex: ha, dossier, mission">
            @error('unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label" for="description">Description détaillée</label>
            <textarea name="description" id="description"
                      class="form-control @error('description') is-invalid @enderror"
                      rows="3">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 d-flex gap-2 pt-3 mt-4" style="border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Enregistrer la prestation
            </button>
            <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection