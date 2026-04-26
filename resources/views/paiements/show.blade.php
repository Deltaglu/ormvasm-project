@extends('layouts.app')

@section('title', 'Paiement '.$paiement->reference)

@section('content')
<x-page-header title="Paiement {{ $paiement->reference }}" subtitle="Détail de l’encaissement et de la quittance.">
    <a href="{{ route('paiements.edit', $paiement) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i>
    </a>
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

<div class="row g-3">
    <div class="col-md-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">Paiement</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5 text-secondary">Référence</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $paiement->reference }}</dd>
                    <dt class="col-sm-5 text-secondary">Montant</dt>
                    <dd class="col-sm-7">{{ number_format($paiement->montant, 2, ',', ' ') }} DH</dd>
                    <dt class="col-sm-5 text-secondary">Date</dt>
                    <dd class="col-sm-7">{{ $paiement->date_paiement->format('d/m/Y') }}</dd>
                    <dt class="col-sm-5 text-secondary">Type</dt>
                    <dd class="col-sm-7"><span class="badge text-bg-light text-dark border">{{ $paiement->type_paiement }}</span></dd>
                    <dt class="col-sm-5 text-secondary">Statut</dt>
                    <dd class="col-sm-7">{{ $paiement->statut }}</dd>
                    <dt class="col-sm-5 text-secondary">N° chèque</dt>
                    <dd class="col-sm-7">{{ $paiement->numero_cheque ?: '—' }}</dd>
                    <dt class="col-sm-5 text-secondary">Agriculteur</dt>
                    <dd class="col-sm-7"><a href="{{ route('agriculteurs.show', $paiement->titreRecette->agriculteur) }}">{{ $paiement->titreRecette->agriculteur->prenom }} {{ $paiement->titreRecette->agriculteur->nom }}</a></dd>
                    <dt class="col-sm-5 text-secondary">Titre</dt>
                    <dd class="col-sm-7"><a href="{{ route('titres-recettes.show', $paiement->titreRecette) }}">{{ $paiement->titreRecette->numero }}</a></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">Quittance</div>
            <div class="card-body">
                @if($paiement->quittance)
                    <p class="mb-2"><span class="text-secondary small">Numéro</span><br><code>{{ $paiement->quittance->numero }}</code></p>
                    <p class="mb-3"><span class="text-secondary small">Montant payé</span><br><span class="fs-5 fw-semibold">{{ number_format($paiement->quittance->montant_paye, 2, ',', ' ') }} DH</span></p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('quittances.show', $paiement->quittance) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('quittances.pdf', $paiement->quittance) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                @else
                    <p class="text-secondary mb-0">Aucune quittance pour ce paiement.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
