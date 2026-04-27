@extends('layouts.app')

@section('title', 'Titres de recette — '.config('app.name'))

@section('content')
<x-page-header title="Titres de recette" subtitle="Suivi des montants, soldes, échéances et pénalités de retard.">
    <a href="{{ route('titres-recettes.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
    <a href="{{ route('titres-recettes.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouveau titre
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap p-4">
    <div class="ormsa-surface-header mb-3 border-bottom-0">
        <i class="bi bi-receipt"></i> Liste des titres de recette
    </div>

    {{-- Restore Custom Search Bar --}}
    <div class="ormsa-table-toolbar mb-4">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative; min-width:320px; flex:1; max-width: 500px;">
                <i class="bi bi-search" style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--gray-500);font-size:1rem;pointer-events:none;z-index:10;"></i>
                <input type="text" id="titreSearchInput"
                       class="form-control form-control-lg shadow-sm"
                       style="padding-left:2.8rem; font-size: 1rem; border-color: var(--gray-300);"
                       placeholder="Rechercher par Numéro ou Agriculteur..."
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
        <table class="table table-hover align-middle mb-0 datatable" id="titreTable">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Émission</th>
                    <th>Échéance</th>
                    <th>Client</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Pénalité</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Solde</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($titresRecettes as $titre)
                    <tr>
                        <td>
                            <code class="small">{{ $titre->numero }}</code>
                            @if($titre->trashed())
                                <span class="badge bg-danger ms-1" style="font-size: 0.6rem; text-transform: uppercase;">Supprimé</span>
                            @endif
                        </td>
                        <td data-order="{{ $titre->date_emission->format('Y-m-d') }}">{{ $titre->date_emission->format('d/m/Y') }}</td>
                        <td data-order="{{ $titre->date_echeance ? $titre->date_echeance->format('Y-m-d') : '9999-12-31' }}">
                            @if($titre->date_echeance)
                                {{ $titre->date_echeance->format('d/m/Y') }}
                                @if($titre->penalite_appliquee)
                                    <span class="status-pill status-pill-danger ms-1" style="font-size:.7rem;padding:.12rem .4rem;">Retard</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $titre->agriculteur?->type === 'society' ? $titre->agriculteur?->nom : ($titre->agriculteur?->prenom . ' ' . $titre->agriculteur?->nom) }}</td>
                        <td class="text-end" data-order="{{ $titre->montant_total }}">{{ number_format($titre->montant_total, 2, ',', ' ') }} DH</td>
                        <td class="text-end @if((float) $titre->montant_penalite > 0) text-danger fw-medium @endif" data-order="{{ $titre->montant_penalite }}">
                            {{ number_format($titre->montant_penalite, 2, ',', ' ') }} DH
                        </td>
                        <td class="text-end fw-semibold" data-order="{{ $titre->montant_total_avec_penalite }}">{{ number_format($titre->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                        <td class="text-end" data-order="{{ $titre->solde_restant }}">{{ number_format($titre->solde_restant, 2, ',', ' ') }} DH</td>
                        <td>
                            <span class="status-pill {{ $titre->statut === 'SOLDE' ? 'status-pill-success' : 'status-pill-warning' }}">
                                {{ $titre->statut }}
                            </span>
                        </td>
                        <td>
                            <div class="ormsa-actions">
                                <a href="{{ route('titres-recettes.show', $titre) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($titre->trashed())
                                    <form action="{{ route('trash.restore', ['type' => 'titre', 'id' => $titre->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-success" type="submit" title="Restaurer">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('trash.force-delete', ['type' => 'titre', 'id' => $titre->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-delete-confirm" type="submit" title="Supprimer Définitivement">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('titres-recettes.edit', $titre) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('titres-recettes.destroy', $titre) }}" method="POST" class="d-inline">
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
    const table = $('#titreTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json' },
        paging: false,
        info: false,
        dom: 'rt',
        order: [[0, 'desc']], // Sort by Numero (newest first)
        columnDefs: [
            { type: 'num', targets: [4, 5, 6, 7] }, // Montant, Penalite, Total, Solde as numbers
            { type: 'date-eu', targets: [1, 2] },  // Émission, Échéance as dates
            { orderable: false, targets: [3, 9] } // Disable sorting on Client and Actions
        ]
    });

    const input = document.getElementById('titreSearchInput');
    const box   = document.getElementById('suggestions');
    let timer;

    input.addEventListener('input', function () {
        const q = this.value.trim();
        table.search(q).draw();

        clearTimeout(timer);
        if (q.length < 2) { box.style.display = 'none'; return; }

        timer = setTimeout(() => {
            fetch('{{ route("titres-recettes.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { box.style.display = 'none'; return; }
                    box.innerHTML = data.map(item => {
                        const agr = item.agriculteur ? item.agriculteur.prenom + ' ' + item.agriculteur.nom : 'N/A';
                        return `<div class="suggestion-item" data-id="${item.id}"><strong>${item.numero}</strong> — ${agr}</div>`;
                    }).join('');
                    box.style.display = 'block';
                    box.querySelectorAll('.suggestion-item').forEach(el => {
                        el.addEventListener('click', () => window.location.href = '/titres-recettes/' + el.dataset.id);
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