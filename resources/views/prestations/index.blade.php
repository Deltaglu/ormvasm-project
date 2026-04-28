@extends('layouts.app')

@section('title', 'Prestations — '.config('app.name'))

@section('content')
<x-page-header title="Prestations" subtitle="Catalogue des prestations facturables et tarifs.">
    <a href="{{ route('prestations.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
    <a href="{{ route('prestations.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouvelle prestation
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap p-4">
    <div class="ormsa-surface-header mb-3 border-bottom-0">
        <i class="bi bi-list-ul"></i> Liste des prestations
    </div>

    {{-- Restore Custom Search Bar --}}
    <div class="ormsa-table-toolbar mb-4">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative; min-width:320px; flex:1; max-width: 500px;">
                <i class="bi bi-search" style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--gray-500);font-size:1rem;pointer-events:none;z-index:10;"></i>
                <input type="text" id="prestationSearchInput"
                       class="form-control form-control-lg shadow-sm"
                       style="padding-left:2.8rem; font-size: 1rem; border-color: var(--gray-300);"
                       placeholder="Rechercher par Code ou Libellé..."
                       autocomplete="off">
                <div id="suggestions" class="search-suggestions shadow-lg border-0" style="font-size: 0.95rem; margin-top: 5px; border-radius: 8px;"></div>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm" style="height: 48px; font-weight: 600;">
                <i class="bi bi-search me-2"></i> Rechercher
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 datatable" id="prestationTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th class="text-end">Tarif unitaire</th>
                    <th>Unité</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestations as $prestation)
                    <tr>
                        <td><code class="small">{{ $prestation->code }}</code></td>
                        <td class="fw-medium">{{ $prestation->libelle }}</td>
                        <td class="text-end fw-semibold" data-order="{{ $prestation->tarif }}">{{ number_format($prestation->tarif, 2, ',', ' ') }} DH</td>
                        <td><span class="badge text-bg-light border text-muted">{{ $prestation->unite ?: '—' }}</span></td>
                        <td class="text-end">
                            <div class="ormsa-actions">
                                <a href="{{ route('prestations.show', $prestation) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('prestations.edit', $prestation) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('prestations.destroy', $prestation) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-delete-confirm" title="Supprimer">
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#prestationTable').DataTable({
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

    const input = document.getElementById('prestationSearchInput');
    const box   = document.getElementById('suggestions');
    let timer;

    input.addEventListener('input', function () {
        const q = this.value.trim();
        table.search(q).draw();

        clearTimeout(timer);
        if (q.length < 2) { box.style.display = 'none'; return; }

        timer = setTimeout(() => {
            fetch('{{ route("prestations.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { box.style.display = 'none'; return; }
                    box.innerHTML = data.map(item => `
                        <div class="suggestion-item" data-id="${item.id}">
                            <strong>${item.code}</strong> — ${item.libelle}
                        </div>
                    `).join('');
                    box.style.display = 'block';
                    box.querySelectorAll('.suggestion-item').forEach(el => {
                        el.addEventListener('click', () => window.location.href = '/prestations/' + el.dataset.id);
                    });
                });
        }, 250);
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
    });
});
</script>
@endpush

@endsection