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

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-people"></i>
        Liste des agriculteurs
    </div>

    {{-- Search toolbar --}}
    <div class="ormsa-table-toolbar">
        <form method="GET" action="{{ route('agriculteurs.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative; min-width:280px; flex:1;">
                <i class="bi bi-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" id="searchInput"
                       class="form-control"
                       style="padding-left:2.2rem;"
                       placeholder="Nom, prénom, CIN ou email…"
                       value="{{ request('search') }}"
                       autocomplete="off">
                <div id="suggestions" class="search-suggestions"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            @if(request('search'))
                <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i> Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
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
            @forelse($agriculteurs as $agriculteur)
                <tr>
                    <td><code>{{ $agriculteur->cin }}</code></td>
                    <td class="fw-medium">{{ $agriculteur->prenom }} {{ $agriculteur->nom }}</td>
                    <td>{{ $agriculteur->telephone ?? '—' }}</td>
                    <td>{{ $agriculteur->email ?? '—' }}</td>
                    <td>
                        <div class="ormsa-actions">
                            <a href="{{ route('agriculteurs.show', $agriculteur) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('agriculteurs.edit', $agriculteur) }}" class="btn btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('agriculteurs.destroy', $agriculteur) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Supprimer"
                                    onclick="return confirm('Supprimer cet agriculteur ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="ormsa-empty">
                            <i class="bi bi-people"></i>
                            Aucun agriculteur trouvé.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="ormsa-pagination">
        {{ $agriculteurs->appends(request()->query())->links() }}
    </div>
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
        }, 280);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
    });
});
</script>
@endpush
@endsection
