@extends('layouts.app')

@section('title', 'Quittance '.$quittance->numero.' — '.config('app.name'))

@section('content')
<x-page-header title="Quittance {{ $quittance->numero }}" subtitle="Récapitulatif aligné sur le document PDF.">
    <a href="{{ route('quittances.pdf', $quittance) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-pdf"></i> Télécharger PDF
    </a>
    <a href="{{ route('quittances.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list"></i> Liste
    </a>
</x-page-header>

@php $p = $quittance->paiement; @endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="ormsa-surface">
            <div class="ormsa-surface-header">
                <i class="bi bi-file-text"></i> Détails de la quittance
            </div>

            <div class="detail-grid">
                <div class="detail-row">
                    <span class="detail-label">Numéro</span>
                    <span class="detail-value fw-bold text-primary">{{ $quittance->numero }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payeur</span>
                    <span class="detail-value fw-semibold">
                        {{ $p->titreRecette->agriculteur->prenom }} {{ $p->titreRecette->agriculteur->nom }}
                        <span class="text-muted fw-normal ms-1">(CIN: {{ $p->titreRecette->agriculteur->cin }})</span>
                    </span>
                </div>
                <div class="detail-row mt-3 pt-3" style="border-top:1px dashed var(--gray-200);">
                    <span class="detail-label">Montant reçu</span>
                    <span class="detail-value fw-bold fs-5 text-success">{{ number_format($p->montant, 2, ',', ' ') }} DH</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date du paiement</span>
                    <span class="detail-value">{{ $p->date_paiement->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mode de règlement</span>
                    <span class="detail-value"><span class="badge text-bg-light border">{{ $p->type_paiement }}</span></span>
                </div>
                
                <div class="detail-row mt-3 pt-3" style="border-top:1px solid var(--border);">
                    <span class="detail-label">Référence paiement</span>
                    <span class="detail-value">
                        <a href="{{ route('paiements.show', $p) }}" class="text-decoration-none fw-medium">{{ $p->reference }}</a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Titre de recette</span>
                    <span class="detail-value">
                        <a href="{{ route('titres-recettes.show', $p->titreRecette) }}" class="text-decoration-none fw-medium">{{ $p->titreRecette->numero }}</a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="ormsa-surface h-100 d-flex flex-column justify-content-center align-items-center p-5 text-center" style="background:var(--gray-50);">
            <div style="width:5rem;height:5rem;border-radius:var(--r-full);background:white;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
                <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
            </div>
            <h4 class="fw-bold mb-2">Document officiel</h4>
            <p class="text-muted mb-4 small" style="line-height:1.6;">
                La quittance de paiement a été générée avec succès.<br>
                Vous pouvez la télécharger au format PDF.
            </p>
            <a href="{{ route('quittances.pdf', $quittance) }}" class="btn btn-primary px-4 py-2">
                <i class="bi bi-download me-2"></i> Télécharger la quittance
            </a>
        </div>
    </div>
</div>
@endsection
