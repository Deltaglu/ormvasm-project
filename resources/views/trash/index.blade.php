@extends('layouts.app')

@section('title', 'Corbeille — ' . config('app.name'))

@section('content')
<x-page-header title="Corbeille" subtitle="Gérez les éléments supprimés et restaurez-les si nécessaire.">
    <div class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Les éléments dans la corbeille ne sont pas visibles dans les listes principales.
    </div>
</x-page-header>

<div class="ormsa-surface p-4">
    <ul class="nav nav-pills mb-4 gap-2" id="trashTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2" id="agri-tab" data-bs-toggle="pill" data-bs-target="#agri" type="button" role="tab">
                <i class="bi bi-people"></i> Agriculteurs <span class="badge bg-light text-dark ms-1">{{ $trashedAgriculteurs->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="paiements-tab" data-bs-toggle="pill" data-bs-target="#paiements" type="button" role="tab">
                <i class="bi bi-cash-stack"></i> Paiements <span class="badge bg-light text-dark ms-1">{{ $trashedPaiements->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="titres-tab" data-bs-toggle="pill" data-bs-target="#titres" type="button" role="tab">
                <i class="bi bi-receipt"></i> Titres <span class="badge bg-light text-dark ms-1">{{ $trashedTitres->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="prestations-tab" data-bs-toggle="pill" data-bs-target="#prestations" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Prestations <span class="badge bg-light text-dark ms-1">{{ $trashedPrestations->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="trashTabsContent">
        {{-- Agriculteurs --}}
        <div class="tab-pane fade show active" id="agri" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>CIN</th>
                            <th>Nom complet</th>
                            <th>Supprimé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedAgriculteurs as $item)
                            <tr>
                                <td><code>{{ $item->cin }}</code></td>
                                <td class="fw-medium">{{ $item->prenom }} {{ $item->nom }}</td>
                                <td class="text-muted small">{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @include('trash._actions', ['type' => 'agriculteur', 'id' => $item->id])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Corbeille vide pour les agriculteurs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paiements --}}
        <div class="tab-pane fade" id="paiements" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Montant</th>
                            <th>Supprimé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedPaiements as $item)
                            <tr>
                                <td><code>{{ $item->reference }}</code></td>
                                <td class="fw-bold">{{ number_format($item->montant, 2, ',', ' ') }} DH</td>
                                <td class="text-muted small">{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @include('trash._actions', ['type' => 'paiement', 'id' => $item->id])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Corbeille vide pour les paiements.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Titres --}}
        <div class="tab-pane fade" id="titres" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Total</th>
                            <th>Supprimé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedTitres as $item)
                            <tr>
                                <td><code>{{ $item->numero }}</code></td>
                                <td class="fw-bold">{{ number_format($item->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                                <td class="text-muted small">{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @include('trash._actions', ['type' => 'titre', 'id' => $item->id])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Corbeille vide pour les titres.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Prestations --}}
        <div class="tab-pane fade" id="prestations" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Supprimé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedPrestations as $item)
                            <tr>
                                <td><code>{{ $item->code }}</code></td>
                                <td class="fw-medium">{{ $item->libelle }}</td>
                                <td class="text-muted small">{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @include('trash._actions', ['type' => 'prestation', 'id' => $item->id])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Corbeille vide pour les prestations.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.nav-pills .nav-link {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    color: var(--gray-600);
    font-weight: 500;
}
.nav-pills .nav-link.active {
    background: var(--c-primary);
    border-color: var(--c-primary);
    color: white;
}
.nav-pills .nav-link:hover:not(.active) {
    background: var(--gray-100);
}
</style>
@endsection
