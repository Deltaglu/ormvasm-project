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

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt"></i> Liste des titres de recette
    </div>

    {{-- Toolbar --}}
    <div class="ormsa-table-toolbar">
        <form method="get" action="{{ route('titres-recettes.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
            <div style="position:relative; min-width:260px; flex:1;">
                <i class="bi bi-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" id="searchInput"
                       class="form-control" style="padding-left:2.2rem;"
                       placeholder="Numéro ou agriculteur…"
                       value="{{ request('search') }}" autocomplete="off">
                <div id="suggestions" class="search-suggestions"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            @if(request('search'))
                <a href="{{ route('titres-recettes.index') }}" class="btn btn-outline-secondary btn-sm">
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
                    <th>Numéro</th>
                    <th>Émission</th>
                    <th>Échéance</th>
                    <th>Agriculteur</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Pénalité</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Payé</th>
                    <th class="text-end">Solde</th>
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
                                    <span class="status-pill status-pill-danger ms-1" style="font-size:.7rem;padding:.12rem .4rem;">Retard</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $titre->agriculteur?->prenom }} {{ $titre->agriculteur?->nom }}</td>
                        <td class="text-end">{{ number_format($titre->montant_total, 2, ',', ' ') }} DH</td>
                        <td class="text-end @if((float) $titre->montant_penalite > 0) text-danger fw-semibold @endif">
                            {{ number_format($titre->montant_penalite, 2, ',', ' ') }} DH
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($titre->montant_total_avec_penalite, 2, ',', ' ') }} DH</td>
                        <td class="text-end">{{ number_format($titre->montant_paye, 2, ',', ' ') }} DH</td>
                        <td class="text-end">{{ number_format($titre->solde_restant, 2, ',', ' ') }} DH</td>
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
                                <a href="{{ route('titres-recettes.edit', $titre) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="ormsa-empty">
                                <i class="bi bi-receipt"></i>
                                Aucun titre de recette trouvé.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ormsa-pagination">{{ $titresRecettes->links() }}</div>
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
        }, 280);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
    });
});
</script>
@endpush
@endsection