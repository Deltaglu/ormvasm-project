@extends('layouts.app')

@section('title', 'Paiements — '.config('app.name'))

@section('content')
<x-page-header title="Paiements" subtitle="Encaissements liés aux titres de recette et quittances générées.">
    <a href="{{ route('paiements.export') }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i>
    </a>
    <a href="{{ route('paiements.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
    </a>
</x-page-header>

<div class="ormsa-surface mb-3">
    <div class="ormsa-surface-header">
        <i class="bi bi-funnel me-2 text-secondary"></i>Filtrer les paiements
    </div>
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-secondary mb-1" for="titre_recette_id">
                    <i class="bi bi-receipt me-1"></i>Filtrer par titre
                </label>
                <select name="titre_recette_id" id="titre_recette_id" class="form-select">
                    <option value="">Tous les titres</option>
                    @foreach($titresRecettes as $titre)
                        <option value="{{ $titre->id }}" @selected(request('titre_recette_id') == $titre->id)>{{ $titre->numero }} — {{ $titre->agriculteur?->prenom }} {{ $titre->agriculteur?->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-secondary mb-1" for="search">
                    <i class="bi bi-search me-1"></i>Rechercher
                </label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Référence, agriculteur..." value="{{ request('search') }}">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-cash-stack me-2 text-secondary"></i>Liste des paiements
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
                        <span class="d-block">{{ $p->titreRecette?->numero }}</span>
                        <small class="text-secondary">{{ $p->titreRecette?->agriculteur?->prenom }} {{ $p->titreRecette?->agriculteur?->nom }}</small>
                    </td>
                    <td>{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td><span class="badge text-bg-light text-dark border">{{ $p->type_paiement }}</span></td>
                    <td>{{ $p->statut }}</td>
                    <td>
                        @if($p->quittance)
                            <code class="small">{{ $p->quittance->numero }}</code>
                        @else
                            <span class="text-secondary">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="ormsa-actions">
                            <a href="{{ route('paiements.show', $p) }}" class="btn btn-outline-secondary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('paiements.edit', $p) }}" class="btn btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($p->quittance)
                                <a href="{{ route('quittances.pdf', $p->quittance) }}" class="btn btn-primary" title="PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            @endif
                            <form action="{{ route('paiements.destroy', $p) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?');">
                                @csrf
                                @method('DELETE')
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
        <div class="card-body border-top ormsa-pagination">{{ $paiements->links() }}</div>
    @endif
</div>
@endsection
