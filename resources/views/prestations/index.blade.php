@extends('layouts.app')

@section('title', 'Prestations — '.config('app.name'))

@section('content')
<x-page-header title="Prestations" subtitle="Catalogue des prestations facturables et tarifs.">
    <a href="{{ route('prestations.export') }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i>
    </a>
    <a href="{{ route('prestations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-list-ul me-2 text-secondary"></i>Liste des prestations
    </div>
    <div class="card-body border-bottom">
        <form method="get" action="{{ route('prestations.index') }}" class="row g-2">
            <div class="col-md-4" style="position: relative;">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Code ou libellé" value="{{ request('search') }}" autocomplete="off" list="none">
                <datalist id="none"></datalist>
                <div id="suggestions" style="position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; width: 100%; border-radius: 0 0 4px 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
            <div class="col-md-2">
                <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x"></i>
                </a>
            </div>
            @endif
        </form>
    </div>
    <div>
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Tarif</th>
                    <th>Unité</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestations as $prestation)
                    <tr>
                        <td><code class="small">{{ $prestation->code }}</code></td>
                        <td class="fw-medium">{{ $prestation->libelle }}</td>
                        <td>{{ number_format($prestation->tarif, 2, ',', ' ') }} DH</td>
                        <td>{{ $prestation->unite ?: '—' }}</td>
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
                    <tr><td colspan="5" class="text-center text-secondary py-5">Aucune prestation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top ormsa-pagination">{{ $prestations->links() }}</div>
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
            var url = '{{ route('prestations.search') }}?q=' + encodeURIComponent(query);
            
            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.length === 0) {
                        suggestionsBox.style.display = 'none';
                        return;
                    }

                    suggestionsBox.innerHTML = data.map(function(item) {
                        return '<div style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + item.id + '">' +
                            '<strong>' + item.code + '</strong> - ' + item.libelle +
                            '</div>';
                    }).join('');
                    suggestionsBox.style.display = 'block';

                    suggestionsBox.querySelectorAll('div[data-id]').forEach(function(div) {
                        div.addEventListener('click', function() {
                            var id = div.getAttribute('data-id');
                            window.location.href = '/prestations/' + id;
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