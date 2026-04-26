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

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-list-ul"></i> Liste des prestations
    </div>

    {{-- Toolbar --}}
    <div class="ormsa-table-toolbar">
        <form method="get" action="{{ route('prestations.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
            <div style="position:relative; min-width:260px; flex:1;">
                <i class="bi bi-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" id="searchInput"
                       class="form-control" style="padding-left:2.2rem;"
                       placeholder="Code ou libellé…"
                       value="{{ request('search') }}" autocomplete="off">
                <div id="suggestions" class="search-suggestions"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            @if(request('search'))
                <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i> Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
                @forelse($prestations as $prestation)
                    <tr>
                        <td><code class="small">{{ $prestation->code }}</code></td>
                        <td class="fw-medium">{{ $prestation->libelle }}</td>
                        <td class="text-end fw-semibold">{{ number_format($prestation->tarif, 2, ',', ' ') }} DH</td>
                        <td><span class="badge text-bg-light border text-muted">{{ $prestation->unite ?: '—' }}</span></td>
                        <td class="text-end">
                            <div class="ormsa-actions">
                                <a href="{{ route('prestations.show', $prestation) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('prestations.edit', $prestation) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="ormsa-empty">
                                <i class="bi bi-list-ul"></i>
                                Aucune prestation trouvée.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="ormsa-pagination">{{ $prestations->links() }}</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('searchInput');
    const box   = document.getElementById('suggestions');
    if (!input || !box) return;
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
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
        }, 280);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
    });
});
</script>
@endpush
@endsection