@extends('layouts.app')

@section('title', 'Modifier campagne')

@section('content')
<x-page-header title="Modifier la campagne" subtitle="Année {{ $campagne->annee }}">
    <a href="{{ route('campagnes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="card-body">
        <form method="post" action="{{ route('campagnes.update', $campagne) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label" for="annee">Année</label>
                <input type="number" name="annee" id="annee" class="form-control" value="{{ old('annee', $campagne->annee) }}" min="1900" max="2100" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $campagne->description) }}</textarea>
            </div>
            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('campagnes.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
