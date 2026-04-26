@extends('layouts.app')

@section('title', 'Quittance '.$quittance->numero)

@section('content')
<x-page-header title="Quittance {{ $quittance->numero }}" subtitle="Récapitulatif aligné sur le document PDF.">
    <a href="{{ route('quittances.pdf', $quittance) }}" class="btn btn-primary">
        <i class="bi bi-download"></i>
    </a>
    <a href="{{ route('quittances.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

@php $p = $quittance->paiement; @endphp

<div class="ormsa-surface">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 col-md-3 text-secondary small">Référence paiement</dt>
            <dd class="col-sm-8 col-md-9"><a href="{{ route('paiements.show', $p) }}">{{ $p->reference }}</a></dd>
            <dt class="col-sm-4 col-md-3 text-secondary small">Payeur</dt>
            <dd class="col-sm-8 col-md-9">{{ $p->titreRecette->agriculteur->prenom }} {{ $p->titreRecette->agriculteur->nom }} <span class="text-secondary">— CIN {{ $p->titreRecette->agriculteur->cin }}</span></dd>
            <dt class="col-sm-4 col-md-3 text-secondary small">Montant reçu</dt>
            <dd class="col-sm-8 col-md-9 fw-semibold fs-5">{{ number_format($p->montant, 2, ',', ' ') }} DH</dd>
            <dt class="col-sm-4 col-md-3 text-secondary small">Date</dt>
            <dd class="col-sm-8 col-md-9">{{ $p->date_paiement->format('d/m/Y') }}</dd>
            <dt class="col-sm-4 col-md-3 text-secondary small">Type</dt>
            <dd class="col-sm-8 col-md-9"><span class="badge text-bg-light text-dark border">{{ $p->type_paiement }}</span></dd>
            <dt class="col-sm-4 col-md-3 text-secondary small">Titre de recette</dt>
            <dd class="col-sm-8 col-md-9"><a href="{{ route('titres-recettes.show', $p->titreRecette) }}">{{ $p->titreRecette->numero }}</a></dd>
        </dl>
    </div>
</div>
@endsection
