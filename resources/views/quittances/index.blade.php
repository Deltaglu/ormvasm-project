@extends('layouts.app')

@section('title', 'Quittances — '.config('app.name'))

@section('content')
<x-page-header title="Quittances" subtitle="Liste des quittances émises pour les paiements enregistrés.">
    <a href="{{ route('quittances.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap p-4 quittances-page">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt-cutoff"></i> Liste des quittances
    </div>

    @if($rg8Quittances->count() > 0)
    {{-- RG8 PDF Export Button --}}
    <div class="px-3 pt-3 pb-0">
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#rg8Modal">
            <i class="bi bi-file-earmark-pdf"></i> RG8 _ Quittance (10 jours)
        </button>
    </div>

    {{-- RG8 Modal --}}
    <div class="modal fade modal-xl" id="rg8Modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; height: 90vh;">
            <div class="modal-content" style="height: 100%;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                        RG8 - Quittances (10 derniers jours)
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('quittances.rg8') }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Télécharger
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="height: calc(100% - 60px);">
                    <iframe src="{{ route('quittances.rg8') }}" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="ormsa-table-toolbar mb-4">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative; min-width:320px; flex:1; max-width: 500px;">
                <i class="bi bi-search" style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--gray-500);font-size:1rem;pointer-events:none;z-index:10;"></i>
                <input type="text" id="quittanceSearchInput"
                       class="form-control form-control-lg shadow-sm"
                       style="padding-left:2.8rem; font-size: 1rem; border-color: var(--gray-300);"
                       placeholder="Rechercher par Numéro ou Client..."
                       autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 datatable" id="quittancesTable">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Réf. paiement</th>
                    <th>Date paiement</th>
                    <th>Client</th>
                    <th>Titre</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quittances as $q)
                    @php $p = $q->paiement; @endphp
                    <tr>
                        <td data-order="{{ $q->numero }}"><code class="small fw-semibold" style="color:var(--c-primary);">{{ $q->numero }}</code></td>
                        <td class="fw-medium text-muted" style="font-size:.85rem;">{{ $p->reference }}</td>
                        <td data-order="{{ $p->date_paiement->format('Y-m-d') }}">{{ $p->date_paiement->format('d/m/Y') }}</td>
                        <td class="fw-medium">{{ $p->titreRecette->agriculteur->type === 'society' ? $p->titreRecette->agriculteur->nom : ($p->titreRecette->agriculteur->prenom . ' ' . $p->titreRecette->agriculteur->nom) }}</td>
                        <td><code class="small text-muted">{{ $p->titreRecette->numero }}</code></td>
                        <td class="text-end fw-semibold" data-order="{{ $p->montant }}">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                        <td class="text-end">
                            <a href="{{ route('quittances.show', $q) }}" class="btn btn-outline-secondary btn-sm" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('quittances.pdf', $q) }}" class="btn btn-outline-primary btn-sm" title="Télécharger PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="ormsa-empty">
                                <i class="bi bi-receipt-cutoff"></i>
                                Aucune quittance trouvée.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
/* ================================================================
   QUITTANCES PAGE - COLUMN ALIGNMENT FIX
   ================================================================ */
#quittancesTable {
    table-layout: fixed !important;
    width: 100% !important;
}

#quittancesTable th,
#quittancesTable td {
    vertical-align: middle !important;
    padding: 12px !important;
    text-align: left !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#quittancesTable th.text-end,
#quittancesTable td.text-end {
    text-align: right !important;
}

/* Fixed column widths */
#quittancesTable th:nth-child(1),
#quittancesTable td:nth-child(1) { width: 12%; }

#quittancesTable th:nth-child(2),
#quittancesTable td:nth-child(2) { width: 14%; }

#quittancesTable th:nth-child(3),
#quittancesTable td:nth-child(3) { width: 12%; }

#quittancesTable th:nth-child(4),
#quittancesTable td:nth-child(4) { width: 22%; }

#quittancesTable th:nth-child(5),
#quittancesTable td:nth-child(5) { width: 12%; }

#quittancesTable th:nth-child(6),
#quittancesTable td:nth-child(6) { width: 14%; }

#quittancesTable th:nth-child(7),
#quittancesTable td:nth-child(7) { width: 14%; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#quittancesTable').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
            lengthMenu: 'Afficher _MENU_ par page'
        },
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Tous']],
        lengthChange: true,
        ordering: false,
        dom: '<"d-flex justify-content-between align-items-center mb-3"><"ms-auto"l>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        info: true
    });

    const input = document.getElementById('quittanceSearchInput');
    if (input) {
        input.addEventListener('input', function () {
            table.search(this.value).draw();
        });
    }
});
</script>
@endpush
@endsection
