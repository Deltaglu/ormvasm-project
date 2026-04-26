@extends('layouts.app')

@section('title', 'Titres de recette — '.config('app.name'))

@section('content')
<x-page-header title="Titres de recette" subtitle="Suivi des montants, soldes, échéances et pénalités de retard.">
    <a href="{{ route('titres-recettes.export') }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i>
    </a>
    <a href="{{ route('titres-recettes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt me-2 text-secondary"></i>Liste des titres de recette
    </div>
    <div class="card-body border-bottom">
        <form method="get" action="{{ route('titres-recettes.index') }}" class="row g-2">
            <div class="col-md-4" style="position: relative;">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Numéro ou agriculteur" value="{{ request('search') }}" autocomplete="off" list="none">
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
                <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary w-100">
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
                    <th>Numéro</th>
                    <th>Date émission</th>
                    <th>Échéance</th>
                    <th>Agriculteur</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Pénalité</th>
                    <th class="text-end">Total</th>
                    <th>Payé</th>
                    <th>Solde</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($titresRecettes as $titre)
                    <tr>
                        <td><code class="small">{{ $titre->numero }}</code></td>
                        <td>{{ $titre->date_emission->format('d/m/Y') }}</td>
                        <td>
                            @if($titre->date_echeance)
                                {{ $titre->date_echeance->format('d/m/Y') }}
                                @if($titre->penalite_appliquee)
                                    <span class="badge text-bg-danger ms-1">Retard</span>
                                @endif
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                        <td>{{ $titre->agriculteur->prenom }} {{ $titre->agriculteur->nom }}</td>
                        <td class="text-end">{{ number_format($titre->montant_total, 2, ',', ' ') }} DH</td>
                        <td class="text-end @if((float) $titre->montant_penalite > 0) text-danger fw-medium @endif">
                            {{ number_format($titre->montant_penalite, 2, ',', ' ') }} DH
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($titre->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                        <td>{{ number_format($titre->montant_paye, 2, ',', ' ') }} DH</td>
                        <td>{{ number_format($titre->solde_restant, 2, ',', ' ') }} DH</td>
                        <td><span class="badge rounded-pill text-bg-{{ $titre->statut === 'SOLDE' ? 'success' : 'warning' }}">{{ $titre->statut }}</span></td>
                        <td class="text-end">
                            <div class="ormsa-actions">
                                <a href="{{ route('titres-recettes.show', $titre) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('titres-recettes.edit', $titre) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-secondary py-5">Aucun titre de recette.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top ormsa-pagination">{{ $titresRecettes->links() }}</div>
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
            var url = '{{ route('titres-recettes.search') }}?q=' + encodeURIComponent(query);
            
            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.length === 0) {
                        suggestionsBox.style.display = 'none';
                        return;
                    }

                    suggestionsBox.innerHTML = data.map(function(item) {
                        var agriculteurName = item.agriculteur ? item.agriculteur.prenom + ' ' + item.agriculteur.nom : 'N/A';
                        return '<div style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + item.id + '">' +
                            '<strong>' + item.numero + '</strong> - ' + agriculteurName +
                            '</div>';
                    }).join('');
                    suggestionsBox.style.display = 'block';

                    suggestionsBox.querySelectorAll('div[data-id]').forEach(function(div) {
                        div.addEventListener('click', function() {
                            var id = div.getAttribute('data-id');
                            window.location.href = '/titres-recettes/' + id;
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