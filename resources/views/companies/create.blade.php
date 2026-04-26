@extends('layouts.app')

@section('title', 'Nouvelle société')

@section('content')
<x-page-header title="Nouvelle société" subtitle="Créer une nouvelle société dans le système." />

<div class="ormsa-surface ormsa-form-card">
    <form method="POST" action="{{ route('companies.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="name" class="form-label">Nom de la société <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Ex: ORMVA Tadla" 
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="code" class="form-label">Code société <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('code') is-invalid @enderror" 
                       id="code" 
                       name="code" 
                       value="{{ old('code') }}" 
                       placeholder="Ex: ORMVA01" 
                       required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="tenant_database" class="form-label">Nom base de données <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('tenant_database') is-invalid @enderror" 
                       id="tenant_database" 
                       name="tenant_database" 
                       value="{{ old('tenant_database') }}" 
                       placeholder="Ex: ormva_tadla_db" 
                       required>
                @error('tenant_database')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Créer la société
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Annuler
                </a>
            </div>
        </div>
    </form>
</div>
@endsection