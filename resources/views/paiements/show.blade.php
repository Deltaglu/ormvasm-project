@extends('layouts.app')

@section('title', 'Paiement '.$paiement->reference)

@section('content')
<x-page-header title="Paiement {{ $paiement->reference }}" subtitle="Détail de l’encaissement et de la quittance.">
    <a href="{{ route('paiements.edit', $paiement) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> Modifier
    </a>
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list"></i> Liste
    </a>
</x-page-header>

<div class="row g-3">
    <div class="col-md-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-cash-stack"></i> Informations du paiement
            </div>
            <div class="detail-grid">
                <div class="detail-row">
                    <span class="detail-label">Référence</span>
                    <span class="detail-value fw-semibold">{{ $paiement->reference }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Montant</span>
                    <span class="detail-value fw-bold text-success" style="font-size:1.1rem;">{{ number_format($paiement->montant, 2, ',', ' ') }} DH</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ $paiement->date_paiement->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type</span>
                    <span class="detail-value"><span class="badge text-bg-light border">{{ $paiement->type_paiement }}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value">
                        <span class="status-pill {{ $paiement->statut === 'VALIDE' ? 'status-pill-success' : 'status-pill-warning' }}">
                            {{ $paiement->statut }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">N° chèque/vir.</span>
                    <span class="detail-value">{{ $paiement->numero_cheque ?: '—' }}</span>
                </div>
                <div class="detail-row mt-3 pt-3" style="border-top:1px dashed var(--gray-200);">
                    <span class="detail-label">Agriculteur</span>
                    <span class="detail-value fw-medium">
                        <a href="{{ route('agriculteurs.show', $paiement->titreRecette->agriculteur) }}" class="text-decoration-none">
                            {{ $paiement->titreRecette->agriculteur->prenom }} {{ $paiement->titreRecette->agriculteur->nom }}
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Titre associé</span>
                    <span class="detail-value">
                        <a href="{{ route('titres-recettes.show', $paiement->titreRecette) }}" class="text-decoration-none fw-medium">
                            {{ $paiement->titreRecette->numero }}
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-file-earmark-text"></i> Quittance
            </div>
            <div class="d-flex flex-column h-100 justify-content-center align-items-center text-center py-4">
                @if($paiement->quittance)
                    <div style="width:4rem;height:4rem;border-radius:var(--r-full);background:var(--c-primary-light);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                        <i class="bi bi-check-lg text-success fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $paiement->quittance->numero }}</h5>
                    <p class="text-muted mb-4">Générée avec succès</p>
                    
                    <div class="p-3 w-100 rounded mb-4" style="background:var(--gray-50);border:1px solid var(--border);">
                        <div class="text-muted small mb-1 text-uppercase fw-bold" style="letter-spacing:.05em;">Montant acquitté</div>
                        <div class="fs-3 fw-bold text-primary">{{ number_format($paiement->quittance->montant_paye, 2, ',', ' ') }} <span class="fs-6 text-muted">DH</span></div>
                    </div>

                    <div class="d-flex gap-2 w-100">
                        <a href="{{ route('quittances.show', $paiement->quittance) }}" class="btn btn-outline-secondary flex-grow-1">
                            <i class="bi bi-eye"></i> Aperçu
                        </a>
                        <a href="{{ route('quittances.pdf', $paiement->quittance) }}" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-file-earmark-pdf"></i> Télécharger PDF
                        </a>
                    </div>
                @else
                    <div class="ormsa-empty">
                        <i class="bi bi-file-earmark-x mb-3 text-muted"></i>
                        <p class="mb-0">Aucune quittance n'a été générée pour ce paiement.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
