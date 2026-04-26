@extends('layouts.app')

@section('title', 'Agriculteurs — '.config('app.name'))

@section('content')
<x-page-header title="Agriculteurs" subtitle="Liste des exploitants agricoles">
    <a href="{{ route('agriculteurs.export') }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i>
    </a>
    <a href="{{ route('agriculteurs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header d-flex align-items-center gap-2">
        <i class="bi bi-people text-secondary"></i>
        Liste des agriculteurs
    </div>
    
    <div class="mb-3">
        <form method="GET" action="{{ route('agriculteurs.index') }}" class="d-flex gap-2">
            <div class="flex-grow-1" style="position: relative;">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Nom, prénom, CIN ou email" value="{{ request('search') }}" autocomplete="off" list="none">
                <datalist id="none"></datalist>
                <div id="suggestions" style="position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; width: 100%; border-radius: 0 0 4px 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('agriculteurs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </form>
    </div>

    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>CIN</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($agriculteurs as $agriculteur)
                <tr>
                    <td class="fw-medium">{{ $agriculteur->cin }}</td>
                    <td>{{ $agriculteur->nom }}</td>
                    <td>{{ $agriculteur->prenom }}</td>
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
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet agriculteur ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">Aucun agriculteur trouvé.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $agriculteurs->appends(request()->query())->links() }}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var suggestionsBox = document.getElementById('suggestions');
    var timeout;

    if (!searchInput || !suggestionsBox) {
        return;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        var query = this.value.trim();

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        timeout = setTimeout(function() {
            var url = '{{ route('agriculteurs.search') }}?q=' + encodeURIComponent(query);
            
            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.length === 0) {
                        suggestionsBox.style.display = 'none';
                        return;
                    }

                    suggestionsBox.innerHTML = data.map(function(item) {
                        return '<div style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + item.id + '">' +
                            '<strong>' + item.cin + '</strong> - ' + item.prenom + ' ' + item.nom +
                            '</div>';
                    }).join('');
                    suggestionsBox.style.display = 'block';

                    suggestionsBox.querySelectorAll('div[data-id]').forEach(function(div) {
                        div.addEventListener('click', function() {
                            var id = div.getAttribute('data-id');
                            window.location.href = '/agriculteurs/' + id;
                        });
                    });
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection
