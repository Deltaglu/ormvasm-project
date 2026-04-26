@extends('layouts.app')

@section('title', 'Quittances — '.config('app.name'))

@section('content')
<x-page-header title="Quittances" subtitle="Liste des quittances émises pour les paiements enregistrés.">
    <a href="{{ route('quittances.export') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel"></i> Exporter
    </a>
</x-page-header>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-receipt-cutoff"></i> Liste des quittances
    </div>

    {{-- Toolbar --}}
    <div class="ormsa-table-toolbar">
        <form method="get" action="{{ route('quittances.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
            <div style="position:relative; min-width:280px; flex:1;">
                <i class="bi bi-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" id="searchInput"
                       class="form-control" style="padding-left:2.2rem;"
                       placeholder="Numéro, référence, agriculteur…"
                       value="{{ request('search') }}" autocomplete="off">
                <div id="suggestions" class="search-suggestions"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
            @if(request('search'))
                <a href="{{ route('quittances.index') }}" class="btn btn-outline-secondary btn-sm">
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
                    <th>Réf. paiement</th>
                    <th>Date paiement</th>
                    <th>Agriculteur</th>
                    <th>Titre</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quittances as $q)
                    @php $p = $q->paiement; @endphp
                    <tr>
                        <td><code class="small fw-semibold" style="color:var(--c-primary);">{{ $q->numero }}</code></td>
                        <td class="fw-medium text-muted" style="font-size:.85rem;">{{ $p->reference }}</td>
                        <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                        <td class="fw-medium">{{ $p->titreRecette->agriculteur->prenom }} {{ $p->titreRecette->agriculteur->nom }}</td>
                        <td><code class="small text-muted">{{ $p->titreRecette->numero }}</code></td>
                        <td class="text-end fw-semibold">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                        <td class="text-end">
                            <div class="ormsa-actions">
                                <a href="{{ route('quittances.show', $q) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('quittances.pdf', $q) }}" class="btn btn-outline-primary" title="Télécharger PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="ormsa-empty">
                                <i class="bi bi-receipt-cutoff"></i>
                                Aucune quittance trouvée.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="ormsa-pagination">{{ $quittances->links() }}</div>
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
            fetch('{{ route("quittances.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { box.style.display = 'none'; return; }
                    box.innerHTML = data.map(item => `
                        <div class="suggestion-item" data-id="${item.id}">
                            <strong>${item.numero}</strong>
                        </div>
                    `).join('');
                    box.style.display = 'block';
                    box.querySelectorAll('.suggestion-item').forEach(el => {
                        el.addEventListener('click', () => window.location.href = '/quittances/' + el.dataset.id);
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
