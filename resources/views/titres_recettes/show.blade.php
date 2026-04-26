@extends('layouts.app')

@section('title', 'Titre '.$titreRecette->numero.' — '.config('app.name'))

@section('content')
<x-page-header title="Titre {{ $titreRecette->numero }}" subtitle="Détail du titre et des paiements associés.">
    <a href="{{ route('titres-recettes.edit', $titreRecette) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> Modifier
    </a>
    <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list"></i> Liste
    </a>
</x-page-header>

<div class="row g-3">
    {{-- Summary card --}}
    <div class="col-lg-4">
        <div class="ormsa-surface">
            <div class="ormsa-surface-header">
                <i class="bi bi-file-text"></i> Récapitulatif
            </div>

            {{-- Status banner --}}
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded"
                 style="background:{{ $titreRecette->statut === 'SOLDE' ? 'var(--c-primary-light)' : 'var(--c-secondary-light)' }};">
                <span class="fw-semibold" style="font-size:.85rem;">Statut</span>
                <span class="status-pill {{ $titreRecette->statut === 'SOLDE' ? 'status-pill-success' : 'status-pill-warning' }}">
                    {{ $titreRecette->statut }}
                </span>
            </div>

            <div class="detail-grid">
                <div class="detail-row">
                    <span class="detail-label">Agriculteur</span>
                    <span class="detail-value fw-semibold">
                        <a href="{{ route('agriculteurs.show', $titreRecette->agriculteur) }}" class="text-decoration-none">
                            {{ $titreRecette->agriculteur->prenom }} {{ $titreRecette->agriculteur->nom }}
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Émission</span>
                    <span class="detail-value">{{ $titreRecette->date_emission->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Échéance</span>
                    <span class="detail-value">
                        {{ $titreRecette->date_echeance?->format('d/m/Y') ?? '—' }}
                        @if($titreRecette->penalite_appliquee)
                            <span class="status-pill status-pill-danger ms-1" style="font-size:.7rem;">Pénalité</span>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Objet</span>
                    <span class="detail-value">{{ $titreRecette->objet ?: '—' }}</span>
                </div>
            </div>

            {{-- Financial summary --}}
            <div class="mt-3 pt-3" style="border-top:1px solid var(--border);">
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Montant principal</span>
                    <span class="fw-medium">{{ number_format($titreRecette->montant_total, 2, ',', ' ') }} DH</span>
                </div>
                @if((float) $titreRecette->montant_penalite > 0)
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Pénalité</span>
                    <span class="fw-medium text-danger">+ {{ number_format($titreRecette->montant_penalite, 2, ',', ' ') }} DH</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-2 pt-2" style="border-top:1px dashed var(--gray-200);font-size:.9rem;">
                    <span class="fw-semibold">Total dû</span>
                    <span class="fw-bold">{{ number_format($titreRecette->montant_total_avec_penalite, 2, ',', ' ') }} DH</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                    <span class="text-muted">Montant payé</span>
                    <span class="fw-medium text-success">{{ number_format($titreRecette->montant_paye, 2, ',', ' ') }} DH</span>
                </div>
                <div class="d-flex justify-content-between pt-2" style="border-top:2px solid var(--border);font-size:.95rem;">
                    <span class="fw-bold">Solde restant</span>
                    <span class="fw-bold {{ (float) $titreRecette->solde_restant > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($titreRecette->solde_restant, 2, ',', ' ') }} DH
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payments table --}}
    <div class="col-lg-8">
        <div class="ormsa-surface ormsa-table-wrap">
            <div class="ormsa-surface-header">
                <i class="bi bi-cash-stack"></i> Paiements associés
                <span class="ms-auto badge text-bg-light">{{ $titreRecette->paiements->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th class="text-end">Montant</th>
                            <th>Type</th>
                            <th>Quittance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($titreRecette->paiements as $paiement)
                            <tr>
                                <td>
                                    <a href="{{ route('paiements.show', $paiement) }}" class="fw-medium text-decoration-none">
                                        {{ $paiement->reference }}
                                    </a>
                                </td>
                                <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($paiement->montant, 2, ',', ' ') }} DH</td>
                                <td><span class="badge text-bg-light">{{ $paiement->type_paiement }}</span></td>
                                <td>
                                    @if($paiement->quittance)
                                        <a href="{{ route('quittances.show', $paiement->quittance) }}" class="btn btn-outline-primary" style="padding:.25rem .5rem;font-size:.8rem;">
                                            <i class="bi bi-eye"></i> {{ $paiement->quittance->numero }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="ormsa-empty">
                                        <i class="bi bi-cash-stack"></i>
                                        Aucun paiement associé.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
