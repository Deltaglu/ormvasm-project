@extends('layouts.app')

@section('title', $agriculteur->prenom.' '.$agriculteur->nom.' — '.config('app.name'))

@section('content')
<x-page-header title="{{ $agriculteur->prenom }} {{ $agriculteur->nom }}" subtitle="Fiche agriculteur — historique des titres et paiements.">
    <a href="{{ route('agriculteurs.edit', $agriculteur) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> Modifier
    </a>
    <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list"></i> Liste
    </a>
</x-page-header>

{{-- Info Card --}}
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-person-vcard"></i> Coordonnées
            </div>
            <div class="detail-grid">
                <div class="detail-row">
                    <span class="detail-label">CIN</span>
                    <span class="detail-value"><code>{{ $agriculteur->cin }}</code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Téléphone</span>
                    <span class="detail-value">{{ $agriculteur->telephone ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $agriculteur->email ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Adresse</span>
                    <span class="detail-value">{{ $agriculteur->adresse ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Titres de recette --}}
<div class="ormsa-surface ormsa-table-wrap mb-4">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt"></i> Titres de recette
        <span class="ms-auto badge text-bg-light">{{ $agriculteur->titresRecettes->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Émission</th>
                    <th>Échéance</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Pénalité</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Solde</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            @forelse($agriculteur->titresRecettes as $titre)
                <tr>
                    <td><a href="{{ route('titres-recettes.show', $titre) }}" class="fw-medium text-decoration-none">{{ $titre->numero }}</a></td>
                    <td>{{ $titre->date_emission->format('d/m/Y') }}</td>
                    <td>{{ $titre->date_echeance ? $titre->date_echeance->format('d/m/Y') : '—' }}</td>
                    <td class="text-end">{{ number_format($titre->montant_total, 2, ',', ' ') }} DH</td>
                    <td class="text-end @if((float) $titre->montant_penalite > 0) text-danger fw-medium @endif">
                        {{ number_format($titre->montant_penalite, 2, ',', ' ') }} DH
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($titre->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                    <td class="text-end">{{ number_format($titre->solde_restant, 2, ',', ' ') }} DH</td>
                    <td>
                        <span class="status-pill {{ $titre->statut === 'SOLDE' ? 'status-pill-success' : 'status-pill-warning' }}">
                            {{ $titre->statut }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="ormsa-empty"><i class="bi bi-receipt"></i>Aucun titre de recette.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Paiements --}}
<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-cash-stack"></i> Historique des paiements
        <span class="ms-auto badge text-bg-light">{{ $agriculteur->paiements->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Titre</th>
                    <th class="text-end">Montant</th>
                    <th>Type</th>
                    <th class="text-end">Quittance</th>
                </tr>
            </thead>
            <tbody>
            @forelse($agriculteur->paiements as $p)
                <tr>
                    <td class="fw-medium">{{ $p->reference }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td><code class="small">{{ $p->titreRecette?->numero }}</code></td>
                    <td class="text-end fw-semibold">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge text-bg-light">{{ $p->type_paiement }}</span></td>
                    <td class="text-end">
                        @if($p->quittance)
                            <a href="{{ route('quittances.show', $p->quittance) }}" class="btn btn-outline-primary" style="padding:.25rem .5rem;font-size:.8rem;">
                                <i class="bi bi-eye"></i>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="ormsa-empty"><i class="bi bi-cash-stack"></i>Aucun paiement.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
