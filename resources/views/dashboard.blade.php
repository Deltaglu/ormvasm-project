@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))

@section('content')
<x-page-header title="Tableau de bord" subtitle="Vue d'ensemble de l'activité et des indicateurs clés." />

{{-- KPI Cards --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="ormsa-stat-label">Utilisateurs</div>
            <div class="ormsa-stat-value">{{ $stats['total_users'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="ormsa-stat-label">Paiements</div>
            <div class="ormsa-stat-value">{{ $stats['total_paiements'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat stat-orange">
            <div class="ormsa-stat-icon"><i class="bi bi-currency-exchange"></i></div>
            <div class="ormsa-stat-label">Montant encaissé</div>
            <div class="ormsa-stat-value" style="font-size:1.3rem;">{{ number_format($stats['total_montant'], 2, ',', ' ') }} <span style="font-size:.8rem;font-weight:600;color:var(--gray-400);">DH</span></div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat stat-orange">
            <div class="ormsa-stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="ormsa-stat-label">Titres</div>
            <div class="ormsa-stat-value">{{ $stats['total_titres'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat stat-orange">
            <div class="ormsa-stat-icon"><i class="bi bi-percent"></i></div>
            <div class="ormsa-stat-label">Pénalités (cumul)</div>
            <div class="ormsa-stat-value" style="font-size:1.3rem;">{{ number_format($stats['total_penalites'], 2, ',', ' ') }} <span style="font-size:.8rem;font-weight:600;color:var(--gray-400);">DH</span></div>
        </div>
    </div>
</div>

{{-- Recent Activity Table --}}
<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header">
        <i class="bi bi-clock-history"></i>
        Activité récente
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Agriculteur</th>
                    <th>Titre</th>
                    <th class="text-end">Montant titre</th>
                    <th class="text-end">Pénalité</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paiement</th>
                    <th class="text-end">Quittance</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentActivity as $p)
                @php $t = $p->titreRecette; @endphp
                <tr>
                    <td class="fw-medium">{{ $p->reference }}</td>
                    <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                    <td>{{ $t?->agriculteur?->prenom }} {{ $t?->agriculteur?->nom }}</td>
                    <td><code class="small">{{ $t?->numero }}</code></td>
                    <td class="text-end">{{ $t ? number_format($t->montant_total, 2, ',', ' ').' DH' : '—' }}</td>
                    <td class="text-end">
                        @if($t && (float) $t->montant_penalite > 0)
                            <span class="status-pill status-pill-danger">{{ number_format($t->montant_penalite, 2, ',', ' ') }} DH</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">{{ $t ? number_format($t->montant_total_avec_penalite, 2, ',', ' ').' DH' : '—' }}</td>
                    <td class="text-end"><span class="status-pill status-pill-success">{{ number_format($p->montant, 2, ',', ' ') }} DH</span></td>
                    <td class="text-end">
                        @if($p->quittance)
                            <a href="{{ route('quittances.show', $p->quittance) }}" class="btn btn-sm btn-outline-primary" title="Voir quittance">
                                <i class="bi bi-eye"></i>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="ormsa-empty">
                            <i class="bi bi-inbox"></i>
                            Aucune activité récente.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
