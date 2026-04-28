@extends('layouts.app')

@section('title', 'Paiements — '.config('app.name'))

@section('content')
<x-page-header title="Paiements" subtitle="Encaissements liés aux titres de recette et quittances générées.">
    <div class="d-flex gap-2">
        <a href="{{ route('paiements.export') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel"></i> Exporter
        </a>
        <a href="{{ route('paiements.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Nouveau paiement
        </a>
    </div>
</x-page-header>

{{-- Table --}}
<div class="ormsa-surface ormsa-table-wrap p-4">
    <div class="ormsa-surface-header mb-3 border-bottom-0">
        <i class="bi bi-cash-stack"></i> Liste des paiements
    </div>

    {{-- Custom Date Filter --}}
    <div class="row g-2 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold">Date de début</label>
            <input type="text" id="minDate" class="form-control datepicker" placeholder="JJ/MM/AAAA">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Date de fin</label>
            <input type="text" id="maxDate" class="form-control datepicker" placeholder="JJ/MM/AAAA">
        </div>
        <div class="col-auto">
            <button id="clearDates" class="btn btn-outline-secondary btn-sm" title="Réinitialiser les dates">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle datatable" id="paiementsTable">
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
                    <td class="fw-medium">
                        {{ $p->reference }}
                        @if($p->trashed())
                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem; text-transform: uppercase;">Supprimé</span>
                        @endif
                    </td>
                    <td data-order="{{ $p->date_paiement->format('Y-m-d') }}">{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>
                        <div class="fw-medium" style="font-size:.85rem;">{{ $p->titreRecette?->numero }}</div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $p->titreRecette?->agriculteur?->type === 'society' ? $p->titreRecette?->agriculteur?->nom : ($p->titreRecette?->agriculteur?->prenom . ' ' . $p->titreRecette?->agriculteur?->nom) }}</div>
                    </td>
                    <td class="text-end fw-semibold" data-order="{{ $p->montant }}">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
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
                            
                            @if($p->trashed())
                                <form action="{{ route('trash.restore', ['type' => 'paiement', 'id' => $p->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-success" type="submit" title="Restaurer">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                <form action="{{ route('trash.force-delete', ['type' => 'paiement', 'id' => $p->id]) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-delete-confirm" type="submit" title="Supprimer Définitivement">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('paiements.edit', $p) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($p->quittance)
                                    <a href="{{ route('quittances.pdf', $p->quittance) }}" class="btn btn-outline-secondary" title="PDF">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    </a>
                                @endif
                                <form action="{{ route('paiements.destroy', $p) }}" method="post" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-delete-confirm" type="button" title="Supprimer">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#paiementsTable').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
            lengthMenu: 'Afficher _MENU_ par page'
        },
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Tous']],
        lengthChange: true,
        ordering: false,
        dom: '<"d-flex justify-content-between align-items-center mb-3"l>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        info: true
    });

    // Custom filtering function which will search data in column four between two values
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'paiementsTable') return true;

        const min = $('#minDate').val();
        const max = $('#maxDate').val();
        const dateStr = data[1]; // Date is in second column (index 1)
        
        if (!min && !max) return true;

        // Convert DD/MM/YYYY to YYYY-MM-DD for comparison
        const parts = dateStr.split('/');
        const current = new Date(parts[2], parts[1] - 1, parts[0]);
        
        if (min) {
            const minParts = min.split('-');
            const minDate = new Date(minParts[0], minParts[1] - 1, minParts[2]);
            if (current < minDate) return false;
        }
        
        if (max) {
            const maxParts = max.split('-');
            const maxDate = new Date(maxParts[0], maxParts[1] - 1, maxParts[2]);
            if (current > maxDate) return false;
        }

        return true;
    });

    // Refilter the table on input change
    $('#minDate, #maxDate').on('change', function () {
        table.draw();
    });

    $('#clearDates').on('click', function() {
        $('#minDate, #maxDate').val('').trigger('change');
        // If using Flatpickr, also clear the altInput
        document.querySelectorAll('.datepicker').forEach(el => {
            if (el._flatpickr) el._flatpickr.clear();
        });
    });
});
</script>
@endpush
@endsection

