@extends('layouts.app')

@section('title', 'Nouvelle société — '.config('app.name'))

@section('content')
<x-page-header title="Nouvelle société" subtitle="Créer un nouveau locataire (tenant) avec sa propre base de données.">
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="ormsa-surface ormsa-form-card">
            <div class="ormsa-surface-header">
                <i class="bi bi-building"></i> Informations de la société
            </div>

            <form method="POST" action="{{ route('companies.store') }}" class="row g-3 p-0">
                @csrf

                <div class="col-12">
                    <label for="name" class="form-label">Nom de la société <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" 
                           value="{{ old('name') }}" 
                           placeholder="Ex: ORMVA Tadla" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label">Code société <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control fw-semibold @error('code') is-invalid @enderror" 
                           id="code" name="code" 
                           value="{{ old('code') }}" 
                           placeholder="Ex: ORMVA01" required>
                    <div class="form-text">Ce code sera utilisé pour la connexion.</div>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="tenant_database" class="form-label">Base de données <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-database"></i></span>
                        <input type="text" 
                               class="form-control border-start-0 @error('tenant_database') is-invalid @enderror" 
                               id="tenant_database" name="tenant_database" 
                               value="{{ old('tenant_database') }}" 
                               placeholder="Ex: ormva_tadla_db" required>
                    </div>
                    <div class="form-text">Nom de la base de données isolée.</div>
                    @error('tenant_database')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2 pt-3 mt-4" style="border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Créer la société
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection