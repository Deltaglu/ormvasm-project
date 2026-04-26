@extends('layouts.app')

@section('title', 'Paiements — '.config('app.name'))

@section('content')
<x-page-header title="Paiements" subtitle="Encaissements liés aux titres de recette et quittances générées.">
    <a href="{{ route('paiements.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
    <a href="{{ route('paiements.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouveau paiement
    </a>
</x-page-header>

{{-- Filters --}}
<div class="ormsa-surface mb-3">
    <div class="ormsa-surface-header">
        <i class="bi bi-funnel"></i> Filtres
    </div>
    <form method="get" class="row g-2 align-items-end p-0">
        <div class="col-md-4">
            <label class="form-label" for="titre_recette_id">Titre de recette</label>
            <select name="titre_recette_id" id="titre_recette_id" class="form-select">
                <option value="">Tous les titres</option>
                @foreach($titresRecettes as $titre)
                    <option value="{{ $titre->id }}" @selected(request('titre_recette_id') == $titre->id)>
                        {{ $titre->numero }} — {{ $titre->agriculteur?->prenom }} {{ $titre->agriculteur?->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="search">Recherche</label>
            <input type="text" name="search" id="search" class="form-control"
                   placeholder="Référence, agriculteur…" value="{{ request('search') }}">
        </div>
        <div class="col-auto d-flex gap-2 align-items-end" style="padding-bottom:0;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-filter"></i> Filtrer
            </button>
            <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x"></i> Réinitialiser
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-cash-stack"></i> Liste des paiements
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Titre / Agriculteur</th>
                    <th class="text-end">Montant</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Quittance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($paiements as $p)
                <tr>
                    <td class="fw-medium">{{ $p->reference }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>
                        <div class="fw-medium" style="font-size:.85rem;">{{ $p->titreRecette?->numero }}</div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $p->titreRecette?->agriculteur?->prenom }} {{ $p->titreRecette?->agriculteur?->nom }}</div>
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge text-bg-light">{{ $p->type_paiement }}</span></td>
                    <td>
                        <span class="status-pill {{ $p->statut === 'VALIDE' ? 'status-pill-success' : 'status-pill-warning' }}">
                            {{ $p->statut }}
                        </span>
                    </td>
                    <td>
                        @if($p->quittance)
                            <code class="small">{{ $p->quittance->numero }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="ormsa-actions">
                            <a href="{{ route('paiements.show', $p) }}" class="btn btn-outline-secondary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('paiements.edit', $p) }}" class="btn btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($p->quittance)
                                <a href="{{ route('quittances.pdf', $p->quittance) }}" class="btn btn-outline-secondary" title="PDF">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                </a>
                            @endif
                            <form action="{{ route('paiements.destroy', $p) }}" method="post" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce paiement ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" type="submit" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($paiements->hasPages())
        <div class="ormsa-pagination">{{ $paiements->links() }}</div>
    @endif
</div>
@endsection
