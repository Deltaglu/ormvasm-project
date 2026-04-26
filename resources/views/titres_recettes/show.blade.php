@extends('layouts.app')

@section('title', 'Titre '.$titreRecette->numero)

@section('content')
<x-page-header title="Titre {{ $titreRecette->numero }}" subtitle="Détail du titre et des paiements associés.">
    <a href="{{ route('titres-recettes.edit', $titreRecette) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i>
    </a>
    <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">Récapitulatif</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5 text-secondary">Agriculteur</dt>
                    <dd class="col-sm-7 fw-medium">{{ $titreRecette->agriculteur->prenom }} {{ $titreRecette->agriculteur->nom }}</dd>
                    <dt class="col-sm-5 text-secondary">Date d’émission</dt>
                    <dd class="col-sm-7">{{ $titreRecette->date_emission->format('d/m/Y') }}</dd>
                    <dt class="col-sm-5 text-secondary">Date d’échéance</dt>
                    <dd class="col-sm-7">
                        @if($titreRecette->date_echeance)
                            {{ $titreRecette->date_echeance->format('d/m/Y') }}
                            @if($titreRecette->penalite_appliquee)
                                <span class="badge text-bg-danger ms-1">Pénalité appliquée</span>
                            @endif
                        @else
                            —
                        @endif
                    </dd>
                    <dt class="col-sm-5 text-secondary">Montant</dt>
                    <dd class="col-sm-7">{{ number_format($titreRecette->montant_total, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Pénalité</dt>
                    <dd class="col-sm-7 @if((float) $titreRecette->montant_penalite > 0) text-danger fw-semibold @endif">
                        {{ number_format($titreRecette->montant_penalite, 2, ',', ' ') }} DH
                    </dd>
                    <dt class="col-sm-5 text-secondary">Total (montant + pénalité)</dt>
                    <dd class="col-sm-7 fw-bold">{{ number_format($titreRecette->montant_total_avec_penalite, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Montant payé</dt>
                    <dd class="col-sm-7">{{ number_format($titreRecette->montant_paye, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Solde restant</dt>
                    <dd class="col-sm-7 fw-semibold">{{ number_format($titreRecette->solde_restant, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Solde + pénalité</dt>
                    <dd class="col-sm-7 fw-semibold">{{ number_format($titreRecette->solde_avec_penalite, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Statut</dt>
                    <dd class="col-sm-7"><span class="badge rounded-pill text-bg-{{ $titreRecette->statut === 'SOLDE' ? 'success' : 'warning' }}">{{ $titreRecette->statut }}</span></dd>
                    <dt class="col-sm-5 text-secondary">Objet</dt>
                    <dd class="col-sm-7">{{ $titreRecette->objet ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="ormsa-surface ormsa-table-wrap h-100">
            <div class="ormsa-surface-header">Paiements associés</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Type</th>
                            <th>Quittance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($titreRecette->paiements as $paiement)
                            <tr>
                                <td><a href="{{ route('paiements.show', $paiement) }}" class="fw-medium">{{ $paiement->reference }}</a></td>
                                <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                <td>{{ number_format($paiement->montant, 2, ',', ' ') }} DH</td>
                                <td><span class="badge text-bg-light text-dark border">{{ $paiement->type_paiement }}</span></td>
                                <td>{{ $paiement->quittance?->numero ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">Aucun paiement associé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
