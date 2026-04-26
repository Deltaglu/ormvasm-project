@extends('layouts.app')

@section('title', 'Modifier titre de recette')

@section('content')
<x-page-header title="Modifier le titre de recette" subtitle="Ajuster les prestations, l'échéance ou l'agriculteur rattaché.">
    <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-form-card">
    <div class="card-body">
        <form method="post" action="{{ route('titres-recettes.update', $titreRecette) }}" class="row g-3" id="titreForm">
            @csrf
            @method('PUT')
            
            <div class="col-md-6">
                <label class="form-label" for="numero">Numéro</label>
                <input type="text" name="numero" id="numero" class="form-control" value="{{ old('numero', $titreRecette->numero) }}" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label" for="date_emission">Date d'émission</label>
                <input type="date" name="date_emission" id="date_emission" class="form-control" value="{{ old('date_emission', $titreRecette->date_emission->format('Y-m-d')) }}" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label" for="date_echeance">Date d'échéance</label>
                <input type="date" name="date_echeance" id="date_echeance" class="form-control" value="{{ old('date_echeance', $titreRecette->date_echeance?->format('Y-m-d')) }}">
                <div class="form-text">Optionnel. Pénalité sur le solde restant après cette date.</div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label" for="agriculteur_id">Agriculteur</label>
                <select name="agriculteur_id" id="agriculteur_id" class="form-select" required>
                    @foreach($agriculteurs as $agriculteur)
                        <option value="{{ $agriculteur->id }}" @selected(old('agriculteur_id', $titreRecette->agriculteur_id) == $agriculteur->id)>{{ $agriculteur->prenom }} {{ $agriculteur->nom }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-12">
                <label class="form-label">Prestations</label>
                <div id="prestations-container" class="mb-3">
                    <div class="row g-2 mb-2 fw-semibold small text-secondary">
                        <div class="col-md-5">Prestation</div>
                        <div class="col-md-3">Quantité</div>
                        <div class="col-md-3">Total (DH)</div>
                        <div class="col-md-1"></div>
                    </div>
                    <!-- Prestations lines will be added here -->
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addPrestationBtn">
                    <i class="bi bi-plus me-1"></i> Ajouter une prestation
                </button>
                @error('prestations')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6">
                <label class="form-label" for="montant_total">Montant total (DH)</label>
                <input type="number" step="0.01" min="0" name="montant_total" id="montant_total" class="form-control" value="{{ old('montant_total', $titreRecette->montant_total) }}" readonly>
                <div class="form-text">Calculé automatiquement à partir des prestations</div>
            </div>
            
            <div class="col-12">
                <label class="form-label" for="objet">Objet</label>
                <textarea name="objet" id="objet" class="form-control" rows="3">{{ old('objet', $titreRecette->objet) }}</textarea>
            </div>
            
            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let prestations = [];
let existingPrestations = [];
let prestationIndex = 0;

function addPrestationLine(prestationId = '', quantity = 1, unitPrice = 0) {
    const container = document.getElementById('prestations-container');
    const index = prestationIndex++;

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 prestation-line';
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
            <input type="number" step="0.01" min="0" class="form-control prestation-line-total" value="0.00" readonly>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removePrestationLine(${index})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <input type="hidden" name="prestations[${index}][unit_price]" class="prestation-unit-price" value="${unitPrice}">
    `;

    container.appendChild(div);
    
    // Auto-fill unit_price from selected prestation
    const select = div.querySelector('.prestation-select');
    const unitPriceInput = div.querySelector('.prestation-unit-price');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        unitPriceInput.value = option.dataset.tarif;
        updatePrestationLine(index);
    } else if (unitPrice > 0) {
        unitPriceInput.value = unitPrice;
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
    
    // Auto-fill unit_price from selected prestation
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
    existingPrestations = {!! $titreRecette->prestations->toJson() ?? '[]' !!};
    console.log('Prestations:', prestations);
    console.log('Existing prestations:', existingPrestations);
    
    document.getElementById('addPrestationBtn').addEventListener('click', () => addPrestationLine());
    
    if (existingPrestations.length > 0) {
        existingPrestations.forEach(p => {
            addPrestationLine(p.id, p.pivot.quantity, p.pivot.unit_price);
        });
    } else {
        addPrestationLine();
    }
});
</script>
@endpush
@endsection