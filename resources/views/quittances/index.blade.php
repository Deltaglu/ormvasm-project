@extends('layouts.app')

@section('title', 'Quittances')

@section('content')
<x-page-header title="Quittances" subtitle="Liste des quittances émises pour les paiements enregistrés.">
    <a href="{{ route('quittances.export') }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt-cutoff me-2 text-secondary"></i>Liste des quittances
    </div>
    <div class="mb-3">
        <form method="GET" action="{{ route('quittances.index') }}" class="d-flex gap-2">
            <div class="flex-grow-1" style="position: relative;">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Numéro, référence, agriculteur..." value="{{ request('search') }}" autocomplete="off" list="none">
                <datalist id="none"></datalist>
                <div id="suggestions" style="position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; width: 100%; border-radius: 0 0 4px 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('quittances.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </form>
    </div>
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Numéro</th>
                <th>Réf. paiement</th>
                <th>Date paiement</th>
                <th>Agriculteur</th>
                <th>Titre</th>
                <th>Montant</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($quittances as $q)
                @php $p = $q->paiement; @endphp
                <tr>
                    <td><code class="small">{{ $q->numero }}</code></td>
                    <td class="fw-medium">{{ $p->reference }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $p->titreRecette->agriculteur->prenom }} {{ $p->titreRecette->agriculteur->nom }}</td>
                    <td><code class="small">{{ $p->titreRecette->numero }}</code></td>
                    <td>{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td class="text-end">
                        <div class="ormsa-actions">
                            <a href="{{ route('quittances.show', $q) }}" class="btn btn-outline-secondary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('quittances.pdf', $q) }}" class="btn btn-primary" title="PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($quittances->hasPages())
        <div class="card-body border-top ormsa-pagination">{{ $quittances->links() }}</div>
    @endif
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
            var url = '{{ route('quittances.search') }}?q=' + encodeURIComponent(query);
            
            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.length === 0) {
                        suggestionsBox.style.display = 'none';
                        return;
                    }

                    suggestionsBox.innerHTML = data.map(function(item) {
                        return '<div style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + item.id + '">' +
                            '<strong>' + item.numero + '</strong>' +
                            '</div>';
                    }).join('');
                    suggestionsBox.style.display = 'block';

                    suggestionsBox.querySelectorAll('div[data-id]').forEach(function(div) {
                        div.addEventListener('click', function() {
                            var id = div.getAttribute('data-id');
                            window.location.href = '/quittances/' + id;
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
