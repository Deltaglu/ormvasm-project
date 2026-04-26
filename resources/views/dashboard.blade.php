@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))

@section('content')
<x-page-header title="Tableau de bord" subtitle="Synthèse des indicateurs et de l'activité récente pour votre société." />

<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="ormsa-stat-label">Utilisateurs</div>
            <div class="ormsa-stat-value">{{ $stats['total_users'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="ormsa-stat-label">Paiements</div>
            <div class="ormsa-stat-value">{{ $stats['total_paiements'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon bg-orange bg-opacity-10 text-orange">
                <i class="bi bi-currency-exchange"></i>
            </div>
            <div class="ormsa-stat-label">Montant encaissé</div>
            <div class="ormsa-stat-value" style="font-size: 1.25rem;">{{ number_format($stats['total_montant'], 2, ',', ' ') }} <span class="fs-6 fw-semibold text-secondary">DH</span></div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon bg-orange bg-opacity-10 text-orange">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="ormsa-stat-label">Titres</div>
            <div class="ormsa-stat-value">{{ $stats['total_titres'] }}</div>
        </div>
    </div>
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon bg-orange bg-opacity-10 text-orange">
                <i class="bi bi-percent"></i>
            </div>
            <div class="ormsa-stat-label">Pénalités (cumul)</div>
            <div class="ormsa-stat-value" style="font-size: 1.25rem;">{{ number_format($stats['total_penalites'], 2, ',', ' ') }} <span class="fs-6 fw-semibold text-secondary">DH</span></div>
        </div>
    </div>
</div>

<div class="ormsa-surface ormsa-table-wrap">
    <div class="ormsa-surface-header d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-secondary"></i>
        Activité récente
    </div>
    <div>
        <table class="table table-hover mb-0 align-middle">
            <thead>
            <tr>
                <th>Référence</th>
                <th>Date</th>
                <th>Agriculteur</th>
                <th>Titre</th>
                <th class="text-end">Montant titre</th>
                <th class="text-end">Pénalité</th>
                <th class="text-end">Total titre</th>
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
                    <td class="text-end">{{ $t ? number_format($t->montant_total, 2, ',', ' ') : '—' }} @if($t) DH @endif</td>
                    <td class="text-end @if($t && (float) $t->montant_penalite > 0) text-danger fw-medium @endif">{{ $t ? number_format($t->montant_penalite, 2, ',', ' ') : '—' }} @if($t) DH @endif</td>
                    <td class="text-end fw-semibold">{{ $t ? number_format($t->montant_total_avec_penalite, 2, ',', ' ') : '—' }} @if($t) DH @endif</td>
                    <td class="text-end">{{ number_format($p->montant, 2, ',', ' ') }} DH</td>
                    <td class="text-end">
                        <div class="ormsa-actions">
                            @if($p->quittance)
                                <a href="{{ route('quittances.show', $p->quittance) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-secondary py-5">Aucune activité récente.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
