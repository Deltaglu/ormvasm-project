@extends('layouts.app')

@section('title', 'Agriculteurs — '.config('app.name'))

@section('content')
<x-page-header title="Agriculteurs" subtitle="Liste des exploitants agricoles enregistrés.">
    <a href="{{ route('agriculteurs.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
    <a href="{{ route('agriculteurs.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouvel agriculteur
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap p-4">
    <div class="ormsa-surface-header mb-3 border-bottom-0">
        <i class="bi bi-people"></i>
        Liste des agriculteurs
    </div>

    {{-- Restore Custom Search Bar --}}
    <div class="ormsa-table-toolbar mb-4">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative; min-width:320px; flex:1; max-width: 500px;">
                <i class="bi bi-search" style="position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--gray-500);font-size:1rem;pointer-events:none;z-index:10;"></i>
                <input type="text" id="customSearchInput"
                       class="form-control form-control-lg shadow-sm"
                       style="padding-left:2.8rem; font-size: 1rem; border-color: var(--gray-300);"
                       placeholder="Rechercher par Nom, CIN ou Téléphone..."
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
        <table class="table table-hover mb-0 align-middle datatable" id="agriTable">
            <thead>
                <tr>
                    <th>CIN</th>
                    <th>Nom complet</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($agriculteurs as $agriculteur)
                <tr>
                    <td><code>{{ $agriculteur->cin }}</code></td>
                    <td class="fw-medium">
                        {{ $agriculteur->prenom }} {{ $agriculteur->nom }}
                        @if($agriculteur->trashed())
                            <span class="badge bg-danger ms-2" style="font-size: 0.65rem; letter-spacing: 0.5px; text-transform: uppercase;">Supprimé</span>
                        @endif
                    </td>
                    <td>{{ $agriculteur->telephone ?? '—' }}</td>
                    <td>{{ $agriculteur->email ?? '—' }}</td>
                    <td>
                        <div class="ormsa-actions">
                            <a href="{{ route('agriculteurs.show', $agriculteur) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @if($agriculteur->trashed())
                                <form action="{{ route('trash.restore', ['type' => 'agriculteur', 'id' => $agriculteur->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-success" type="submit" title="Restaurer">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                <form action="{{ route('trash.force-delete', ['type' => 'agriculteur', 'id' => $agriculteur->id]) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-delete-confirm" type="submit" title="Supprimer Définitivement">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('agriculteurs.edit', $agriculteur) }}" class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('agriculteurs.destroy', $agriculteur) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-delete-confirm" title="Supprimer">
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
    // Initialize DataTable without the default search box
    const table = $('#agriTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json' },
        paging: false,
        info: false,
        dom: 'rt', 
        order: []
    });

    const input = document.getElementById('customSearchInput');
    const box   = document.getElementById('suggestions');
    let timer;

    input.addEventListener('input', function () {
        const q = this.value.trim();
        
        // 1. Filter the Table instantly
        table.search(q).draw();

        // 2. Handle Suggestions
        clearTimeout(timer);
        if (q.length < 2) { box.style.display = 'none'; return; }

        timer = setTimeout(() => {
            fetch('{{ route("agriculteurs.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { box.style.display = 'none'; return; }
                    box.innerHTML = data.map(item =>
                        `<div class="suggestion-item" data-id="${item.id}">
                            <strong>${item.cin}</strong> — ${item.prenom} ${item.nom}
                        </div>`
                    ).join('');
                    box.style.display = 'block';
                    box.querySelectorAll('.suggestion-item').forEach(el => {
                        el.addEventListener('click', () => {
                            window.location.href = '/agriculteurs/' + el.dataset.id;
                        });
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
