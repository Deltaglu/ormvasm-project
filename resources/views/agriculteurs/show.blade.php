@extends('layouts.app')

@section('title', $agriculteur->prenom.' '.$agriculteur->nom.' — '.config('app.name'))

@section('content')
<x-page-header title="{{ $agriculteur->prenom }} {{ $agriculteur->nom }}" subtitle="Fiche agriculteur et historique des titres et paiements.">
    <a href="{{ route('agriculteurs.edit', $agriculteur) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i>
    </a>
    <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-person-vcard me-2 text-secondary"></i>Coordonnées
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-secondary">CIN</dt>
                    <dd class="col-sm-8"><code>{{ $agriculteur->cin }}</code></dd>
                    <dt class="col-sm-4 text-secondary">Téléphone</dt>
                    <dd class="col-sm-8">{{ $agriculteur->telephone ?? '—' }}</dd>
                    <dt class="col-sm-4 text-secondary">E-mail</dt>
                    <dd class="col-sm-8">{{ $agriculteur->email ?? '—' }}</dd>
                    <dt class="col-sm-4 text-secondary">Adresse</dt>
                    <dd class="col-sm-8">{{ $agriculteur->adresse ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="ormsa-surface ormsa-table-wrap mb-4">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt me-2 text-secondary"></i>Titres de recette
    </div>
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Date émission</th>
                <th>Échéance</th>
                <th class="text-end">Montant</th>
                <th class="text-end">Pénalité</th>
                <th class="text-end">Total</th>
                <th>Solde</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>
            @forelse($agriculteur->titresRecettes as $titre)
                <tr>
                    <td><a href="{{ route('titres-recettes.show', $titre) }}" class="fw-medium">{{ $titre->numero }}</a></td>
                    <td>{{ $titre->date_emission->format('d/m/Y') }}</td>
                    <td>{{ $titre->date_echeance ? $titre->date_echeance->format('d/m/Y') : '—' }}</td>
                    <td class="text-end">{{ number_format($titre->montant_total, 2, ',', ' ') }} DH</td>
                    <td class="text-end @if((float) $titre->montant_penalite > 0) text-danger @endif">{{ number_format($titre->montant_penalite, 2, ',', ' ') }} DH</td>
                    <td class="text-end fw-medium">{{ number_format($titre->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                    <td>{{ number_format($titre->solde_restant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge rounded-pill text-bg-{{ $titre->statut === 'SOLDE' ? 'success' : 'warning' }}">{{ $titre->statut }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">Aucun titre de recette.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-cash-stack me-2 text-secondary"></i>Historique des paiements
    </div>
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Référence</th>
                <th>Date</th>
                <th>Titre</th>
                <th>Montant</th>
                <th>Type</th>
                <th>Quittance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($agriculteur->paiements as $p)
                <tr>
                    <td class="fw-medium">{{ $p->reference }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td><code class="small">{{ $p->titreRecette?->numero }}</code></td>
                    <td>{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge text-bg-light text-dark border">{{ $p->type_paiement }}</span></td>
                    <td>
                        <div class="ormsa-actions">
                            @if($p->quittance)
                                <a href="{{ route('quittances.show', $p->quittance) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Aucun paiement.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
