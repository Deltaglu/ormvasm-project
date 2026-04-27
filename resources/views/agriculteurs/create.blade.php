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
            <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required onchange="togglePrenomField()">
                <option value="individual">Particulier</option>
                <option value="society">Société</option>
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="parent_id">Société parente</label>
            <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                <option value="">-- Aucune --</option>
                @foreach(\App\Models\Agriculteur::where('type', 'society')->get() as $society)
                    <option value="{{ $society->id }}" {{ (old('parent_id', $parent?->id) == $society->id) ? 'selected' : '' }}>{{ $society->nom }}</option>
                @endforeach
            </select>
            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="nom"
                   class="form-control @error('nom') is-invalid @enderror"
                   value="{{ old('nom') }}" required placeholder="Nom de famille ou raison sociale">
            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6" id="prenom-field">
            <label class="form-label" for="prenom">Prénom</label>
            <input type="text" name="prenom" id="prenom"
                   class="form-control @error('prenom') is-invalid @enderror"
                   value="{{ old('prenom') }}" placeholder="Prénom">
            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6" id="cin-field">
            <label class="form-label" for="cin">CIN <span class="text-danger" id="cin-required">*</span></label>
            <input type="text" name="cin" id="cin"
                   class="form-control @error('cin') is-invalid @enderror"
                   value="{{ old('cin') }}" placeholder="Numéro CIN unique">
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

@push('scripts')
<script>
function togglePrenomField() {
    const type = document.getElementById('type').value;
    const prenomField = document.getElementById('prenom-field');
    const prenomInput = document.getElementById('prenom');
    const cinField = document.getElementById('cin-field');
    const cinInput = document.getElementById('cin');
    const cinRequired = document.getElementById('cin-required');
    
    if (type === 'society') {
        prenomField.style.display = 'none';
        prenomInput.value = '';
        cinField.style.display = 'none';
        cinInput.value = '';
    } else {
        prenomField.style.display = 'block';
        cinField.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePrenomField();
});
</script>
@endpush
@endsection
