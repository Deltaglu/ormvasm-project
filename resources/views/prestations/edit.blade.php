@extends('layouts.app')

@section('title', 'Modifier prestation')

@section('content')
<x-page-header title="Modifier la prestation" subtitle="Mettre à jour le tarif ou le libellé.">
    <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="card-body">
        <form method="post" action="{{ route('prestations.update', $prestation) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label" for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $prestation->code) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="libelle">Libellé</label>
                <input type="text" name="libelle" id="libelle" class="form-control" value="{{ old('libelle', $prestation->libelle) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="tarif">Tarif (DH)</label>
                <input type="number" step="0.01" min="0" name="tarif" id="tarif" class="form-control" value="{{ old('tarif', $prestation->tarif) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="unite">Unité</label>
                <input type="text" name="unite" id="unite" class="form-control" value="{{ old('unite', $prestation->unite) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $prestation->description) }}</textarea>
            </div>
            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
