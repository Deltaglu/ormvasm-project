@extends('layouts.app')

@section('title', 'Nouveau titre de recette')

@section('content')
<x-page-header title="Nouveau titre de recette" subtitle="Émettre un titre avec des lignes de prestations.">
    <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt"></i> Informations du titre
    </div>

    <form method="post" action="{{ route('titres-recettes.store') }}" class="row g-3 p-0" id="titreForm">
        @csrf
        
        <div class="col-md-6">
            <label class="form-label" for="numero">Numéro <span class="text-muted fw-normal">(auto-généré si vide)</span></label>
            <input type="text" name="numero" id="numero" class="form-control" value="{{ old('numero') }}" placeholder="Ex: TR20250001">
        </div>
        
        <div class="col-md-6">
            <label class="form-label" for="date_emission">Date d'émission <span class="text-danger">*</span></label>
            <input type="date" name="date_emission" id="date_emission" class="form-control" value="{{ old('date_emission', date('Y-m-d')) }}" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label" for="date_echeance">Date d'échéance</label>
            <input type="date" name="date_echeance" id="date_echeance" class="form-control" value="{{ old('date_echeance') }}">
            <div class="form-text">Optionnel. Pénalité appliquée après cette date.</div>
        </div>
        
        <div class="col-md-6">
            <label class="form-label" for="agriculteur_id">Agriculteur <span class="text-danger">*</span></label>
            <select name="agriculteur_id" id="agriculteur_id" class="form-select" required>
                <option value="">— Sélectionner —</option>
                @foreach($agriculteurs as $agriculteur)
                    <option value="{{ $agriculteur->id }}" @selected(old('agriculteur_id') == $agriculteur->id)>
                        {{ $agriculteur->prenom }} {{ $agriculteur->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-12 mt-4 pt-3" style="border-top:1px dashed var(--border);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label mb-0 fw-bold">Lignes de prestation <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addPrestationBtn">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
            </div>

            <div id="prestations-container">
                <div class="row g-2 mb-2 pb-2" style="font-size:.75rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;border-bottom:1px solid var(--border);">
                    <div class="col-md-5">Prestation</div>
                    <div class="col-md-3">Quantité</div>
                    <div class="col-md-3">Total (DH)</div>
                    <div class="col-md-1"></div>
                </div>
                <!-- Lignes ajoutées dynamiquement -->
            </div>
            @error('prestations')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mt-4">
            <label class="form-label fw-bold" for="montant_total">Montant total calculé</label>
            <div class="input-group">
                <input type="number" step="0.01" min="0" name="montant_total" id="montant_total" 
                       class="form-control fw-bold text-primary" style="font-size:1.1rem;" 
                       value="0.00" readonly>
                <span class="input-group-text fw-bold text-primary">DH</span>
            </div>
        </div>
        
        <div class="col-12 mt-3">
            <label class="form-label" for="objet">Objet / Note</label>
            <textarea name="objet" id="objet" class="form-control" rows="2">{{ old('objet') }}</textarea>
        </div>
        
        <div class="col-12 d-flex gap-2 pt-3 mt-4" style="border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Créer le titre
            </button>
            <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let prestations = [];
let prestationIndex = 0;

function addPrestationLine(prestationId = '', quantity = 1) {
    const container = document.getElementById('prestations-container');
    const index = prestationIndex++;

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center prestation-line';
    div.dataset.index = index;

    let options = '<option value="">— Sélectionner —</option>';
    prestations.forEach(p => {
        options += `<option value="${p.id}" data-tarif="${p.tarif}" data-unite="${p.unite}" ${p.id == prestationId ? 'selected' : ''}>${p.libelle} (${p.tarif} DH/${p.unite})</option>`;
    });

    div.innerHTML = `
        <div class="col-md-5">
            <select name="prestations[${index}][prestation_id]" class="form-select prestation-select" required onchange="updatePrestationLine(${index})">
                ${options}
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" min="0.01" name="prestations[${index}][quantity]" class="form-control prestation-quantity" value="${quantity}" required onchange="updatePrestationLine(${index})">
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" min="0" class="form-control prestation-line-total bg-light" value="0.00" readonly>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removePrestationLine(${index})" title="Supprimer">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <input type="hidden" name="prestations[${index}][unit_price]" class="prestation-unit-price" value="0">
    `;

    container.appendChild(div);
    
    const select = div.querySelector('.prestation-select');
    const unitPriceInput = div.querySelector('.prestation-unit-price');
    if (select.value) {
        const option = select.options[select.selectedIndex];
        unitPriceInput.value = option.dataset.tarif;
        updatePrestationLine(index);
    }
    calculateTotal();
}

function removePrestationLine(index) {
    const line = document.querySelector(`.prestation-line[data-index="${index}"]`);
    if (line) {
        line.remove();
        calculateTotal();
    }
}

function updatePrestationLine(index) {
    const line = document.querySelector(`.prestation-line[data-index="${index}"]`);
    if (!line) return;

    const select = line.querySelector('.prestation-select');
    const quantityInput = line.querySelector('.prestation-quantity');
    const unitPriceInput = line.querySelector('.prestation-unit-price');
    const totalInput = line.querySelector('.prestation-line-total');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        unitPriceInput.value = option.dataset.tarif;
    }
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const total = quantity * unitPrice;

    totalInput.value = total.toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    const lines = document.querySelectorAll('.prestation-line');
    let total = 0;
    lines.forEach(line => {
        const totalInput = line.querySelector('.prestation-line-total');
        total += parseFloat(totalInput.value) || 0;
    });
    document.getElementById('montant_total').value = total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    prestations = {!! $prestations->toJson() ?? '[]' !!};
    document.getElementById('addPrestationBtn').addEventListener('click', () => addPrestationLine());
    addPrestationLine();
});
</script>
@endpush
@endsection