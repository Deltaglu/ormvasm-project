@extends('layouts.app')

@section('title', 'Paramètres — '.config('app.name'))

@section('content')
<x-page-header title="Paramètres" subtitle="Taux de pénalité de retard appliqué au solde restant après la date d'échéance d'un titre." />

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ormsa-surface ormsa-form-card">
            <div class="ormsa-surface-header">Pénalités de retard</div>
            <div class="card-body">
                <p class="text-secondary small mb-4">
                    Les deux pénalités s'appliquent <strong>simultanément</strong> sur le <strong>solde restant</strong> du titre lorsque la date du jour est <strong>postérieure</strong> à la date d'échéance.
                    Les montants sont recalculés automatiquement (liste des titres, tableau de bord, après paiement).
                </p>

                <form method="post" action="{{ route('settings.update') }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    {{-- Monthly Recurring Rate --}}
                    <div class="col-12">
                        <label class="form-label" for="monthly_penalty_rate">Taux mensuel récurrent (%)</label>
                        <div class="input-group" style="max-width: 14rem;">
                            <input type="number" step="0.01" min="0" max="100" name="monthly_penalty_rate" id="monthly_penalty_rate"
                                   class="form-control @error('monthly_penalty_rate') is-invalid @enderror"
                                   value="{{ old('monthly_penalty_rate', $setting->monthly_penalty_rate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Appliqué chaque 30 jours après la date d'échéance.</div>
                        @error('monthly_penalty_rate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- One-time Rate --}}
                    <div class="col-12">
                        <label class="form-label" for="one_time_penalty_rate">Taux unique après 2 mois (%)</label>
                        <div class="input-group" style="max-width: 14rem;">
                            <input type="number" step="0.01" min="0" max="100" name="one_time_penalty_rate" id="one_time_penalty_rate"
                                   class="form-control @error('one_time_penalty_rate') is-invalid @enderror"
                                   value="{{ old('one_time_penalty_rate', $setting->one_time_penalty_rate) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Appliqué une seule fois si le retard est ≥ 2 mois.</div>
                        @error('one_time_penalty_rate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="col-lg-6">
        <div class="ormsa-surface ormsa-form-card">
            <div class="ormsa-surface-header">Informations</div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary">
                        <i class="bi bi-calendar-repeat me-2"></i>Majoration mensuelle
                    </h6>
                    <p class="text-secondary small mb-0">
                        S'applique dès le premier jour de retard et <strong>augmente chaque 30 jours</strong>.<br>
                        <strong>Formule :</strong> Solde restant × Taux mensuel × Nombre de mois de retard
                    </p>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary">
                        <i class="bi bi-lightning-charge me-2"></i>Pénalité unique
                    </h6>
                    <p class="text-secondary small mb-0">
                        S'ajoute <strong>une seule fois</strong> lorsque le retard atteint <strong>2 mois</strong>.<br>
                        <strong>Formule :</strong> Solde restant × Taux unique
                    </p>
                </div>
                <hr>
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Les deux pénalités sont cumulées. Exemple : solde 1000 DH, 3 mois de retard, taux 5% mensuel + 2% unique = 150 + 20 = <strong>170 DH</strong>.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection