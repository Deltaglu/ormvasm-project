@extends('layouts.app')

@section('title', ($agriculteur->prenom ? $agriculteur->prenom.' ' : '').$agriculteur->nom.' — '.config('app.name'))

@section('content')
<x-page-header title="{{ $agriculteur->prenom ? $agriculteur->prenom.' ' : '' }}{{ $agriculteur->nom }}" subtitle="Fiche agriculteur — historique des titres et paiements.">
    <div class="d-flex gap-2">
        @if($agriculteur->trashed())
            <form action="{{ route('trash.restore', ['type' => 'agriculteur', 'id' => $agriculteur->id]) }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurer
                </button>
            </form>
        @endif
        @if($agriculteur->type === 'society')
            <a href="{{ route('agriculteurs.create') }}?parent_id={{ $agriculteur->id }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Ajouter membre
            </a>
        @endif
        <a href="{{ route('agriculteurs.releve', $agriculteur) }}" class="btn btn-outline-primary btn-sm btn-preview-pdf">
            <i class="bi bi-printer"></i> Imprimer Relevé
        </a>
        <a href="{{ route('agriculteurs.edit', $agriculteur) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list"></i> Liste
        </a>
    </div>
</x-page-header>

{{-- Parent Society --}}
@if($agriculteur->parent)
<div class="alert alert-info mb-3">
    <i class="bi bi-building"></i> Membre de la société : <a href="{{ route('agriculteurs.show', $agriculteur->parent) }}" class="alert-link fw-semibold">{{ $agriculteur->parent->nom }}</a>
</div>
@endif

{{-- Info Card --}}
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-person-vcard"></i> Coordonnées
            </div>
            <div class="detail-grid">
                <div class="detail-row">
                    <span class="detail-label">Type</span>
                    <span class="detail-value">
                        <span class="badge {{ $agriculteur->type === 'society' ? 'text-bg-primary' : 'text-bg-secondary' }}">
                            {{ $agriculteur->type === 'society' ? 'Société' : 'Particulier' }}
                        </span>
                    </span>
                </div>
                @if($agriculteur->cin)
                <div class="detail-row">
                    <span class="detail-label">CIN</span>
                    <span class="detail-value"><code>{{ $agriculteur->cin }}</code></span>
                </div>
                @endif
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

{{-- Children (for societies) --}}
@if($agriculteur->type === 'society' && $agriculteur->children->count() > 0)
<div class="ormsa-surface ormsa-table-wrap mb-4">
    <div class="ormsa-surface-header">
        <i class="bi bi-people"></i> Membres de la société
        <span class="ms-auto badge text-bg-light">{{ $agriculteur->children->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>CIN</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($agriculteur->children as $child)
                <tr class="{{ $child->trashed() ? 'table-danger bg-opacity-10' : '' }}">
                    <td class="fw-medium">{{ $child->nom }}</td>
                    <td>{{ $child->prenom ?? '—' }}</td>
                    <td><code>{{ $child->cin ?? '—' }}</code></td>
                    <td>{{ $child->telephone ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1 align-items-center">
                            @if($child->trashed())
                                <span class="badge bg-danger" style="font-size: 0.65rem; letter-spacing: 0.5px; text-transform: uppercase;">Supprimé</span>
                            @endif
                            <a href="{{ route('agriculteurs.show', $child) }}" class="btn btn-outline-primary btn-sm" style="padding:.25rem .5rem;" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($child->trashed())
                                <form action="{{ route('trash.restore', ['type' => 'agriculteur', 'id' => $child->id]) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" style="padding:.25rem .5rem;" title="Restaurer">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                <form action="{{ route('trash.force-delete', ['type' => 'agriculteur', 'id' => $child->id]) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" style="padding:.25rem .5rem;" title="Supprimer Définitivement">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('agriculteurs.edit', $child) }}" class="btn btn-outline-warning btn-sm" style="padding:.25rem .5rem;" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('agriculteurs.destroy', $child) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-confirm" style="padding:.25rem .5rem;" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

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
