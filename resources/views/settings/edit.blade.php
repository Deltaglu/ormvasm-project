@extends('layouts.app')

@section('title', 'Paramètres — '.config('app.name'))

@section('content')
<x-page-header title="Paramètres" subtitle="Taux de pénalité de retard appliqués aux titres de recette." />

<div class="row g-4">
    {{-- Form card --}}
    <div class="col-lg-6">
        <div class="ormsa-surface">
            <div class="ormsa-surface-header">
                <i class="bi bi-sliders"></i> Pénalités de retard
            </div>

            <p class="text-muted mb-4" style="font-size:.85rem;line-height:1.7;">
                Les deux pénalités s'appliquent <strong>simultanément</strong> sur le <strong>solde restant</strong>
                du titre lorsque la date du jour est <strong>postérieure</strong> à la date d'échéance.
                Les montants sont recalculés automatiquement après chaque paiement.
            </p>

            <form method="post" action="{{ route('settings.update') }}" class="row g-3">
                @csrf @method('PUT')

                <div class="col-12">
                    <label class="form-label" for="monthly_penalty_rate">
                        Taux mensuel récurrent <span class="text-danger">*</span>
                    </label>
                    <div class="input-group" style="max-width:14rem;">
                        <input type="number" step="0.01" min="0" max="100"
                               name="monthly_penalty_rate" id="monthly_penalty_rate"
                               class="form-control @error('monthly_penalty_rate') is-invalid @enderror"
                               value="{{ old('monthly_penalty_rate', $setting->monthly_penalty_rate) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Appliqué chaque 30 jours après la date d'échéance.</div>
                    @error('monthly_penalty_rate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="one_time_penalty_rate">
                        Taux unique après 2 mois <span class="text-danger">*</span>
                    </label>
                    <div class="input-group" style="max-width:14rem;">
                        <input type="number" step="0.01" min="0" max="100"
                               name="one_time_penalty_rate" id="one_time_penalty_rate"
                               class="form-control @error('one_time_penalty_rate') is-invalid @enderror"
                               value="{{ old('one_time_penalty_rate', $setting->one_time_penalty_rate) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Appliqué une seule fois si le retard est ≥ 2 mois.</div>
                    @error('one_time_penalty_rate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 pt-2" style="border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Info card --}}
    <div class="col-lg-6">
        <div class="ormsa-surface">
            <div class="ormsa-surface-header">
                <i class="bi bi-info-circle"></i> Informations
            </div>

            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:2rem;height:2rem;border-radius:var(--r-sm);background:var(--c-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-calendar-repeat text-primary"></i>
                    </div>
                    <h6 class="fw-bold mb-0" style="font-size:.9rem;">Majoration mensuelle</h6>
                </div>
                <p class="text-muted mb-0" style="font-size:.85rem;line-height:1.7;padding-left:2.5rem;">
                    S'applique dès le premier jour de retard et <strong>augmente chaque 30 jours</strong>.<br>
                    <strong>Formule :</strong> Solde restant × Taux mensuel × Nombre de mois
                </p>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:1.25rem;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:2rem;height:2rem;border-radius:var(--r-sm);background:var(--c-secondary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-lightning-charge" style="color:var(--c-secondary);"></i>
                    </div>
                    <h6 class="fw-bold mb-0" style="font-size:.9rem;">Pénalité unique</h6>
                </div>
                <p class="text-muted mb-0" style="font-size:.85rem;line-height:1.7;padding-left:2.5rem;">
                    S'ajoute <strong>une seule fois</strong> lorsque le retard atteint <strong>2 mois</strong>.<br>
                    <strong>Formule :</strong> Solde restant × Taux unique
                </p>
            </div>

            <div class="mt-4 p-3 rounded" style="background:var(--gray-50);border:1px solid var(--border);font-size:.82rem;color:var(--gray-500);">
                <i class="bi bi-lightbulb me-1 text-warning"></i>
                <strong>Exemple :</strong> Solde 1 000 DH, 3 mois de retard, taux 5%/mois + 2% unique
                = <strong style="color:var(--gray-700);">150 + 20 = 170 DH</strong> de pénalité.
            </div>
        </div>
    </div>
</div>
@endsection