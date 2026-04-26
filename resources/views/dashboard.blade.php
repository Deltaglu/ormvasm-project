@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))

@section('content')
<x-page-header title="Tableau de bord" subtitle="Vue d'ensemble de l'activité et des indicateurs clés." />

{{-- KPI Cards --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="ormsa-stat-label">Agriculteurs</div>
            <div class="ormsa-stat-value">{{ $stats['total_agriculteurs'] }}</div>
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

{{-- Charts Row --}}
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-graph-up-arrow"></i> Recouvrement (12 derniers mois)
            </div>
            <div class="p-3">
                <div id="revenueChart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="row g-4 h-100">
            <div class="col-12 h-50">
                <div class="ormsa-surface h-100">
                    <div class="ormsa-surface-header">
                        <i class="bi bi-pie-chart-fill"></i> Prestations (Top 5)
                    </div>
                    <div class="p-3 d-flex align-items-center justify-content-center h-100" style="min-height: 220px;">
                        <div id="prestationsChart" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 h-50">
                <div class="ormsa-surface h-100">
                    <div class="ormsa-surface-header">
                        <i class="bi bi-bar-chart-fill"></i> Encaissé vs Pénalités
                    </div>
                    <div class="p-3 d-flex align-items-center justify-content-center h-100" style="min-height: 220px;">
                        <div id="encaissesChart" class="w-100"></div>
                    </div>
                </div>
            </div>
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

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Chart (Line)
    const revenueData = @json($revenueChartData);
    const revenueOptions = {
        series: [{
            name: 'Paiements',
            data: revenueData.series
        }],
        chart: {
            height: 350,
            type: 'area',
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#0ea5e9'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.0,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: revenueData.labels,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return value.toLocaleString('fr-FR') + " DH";
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
        }
    };
    new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();

    // 2. Prestations Chart (Pie)
    const prestationsData = @json($prestationsChartData);
    const prestationsOptions = {
        series: prestationsData.series,
        labels: prestationsData.labels,
        chart: {
            type: 'donut',
            height: 250,
            fontFamily: 'Inter, sans-serif',
        },
        colors: ['#0ea5e9', '#f97316', '#10b981', '#6366f1', '#f43f5e'],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '13px'
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toLocaleString('fr-FR') + " DH";
                }
            }
        }
    };
    if (prestationsData.series.length > 0) {
        new ApexCharts(document.querySelector("#prestationsChart"), prestationsOptions).render();
    } else {
        document.querySelector("#prestationsChart").innerHTML = '<div class="text-muted text-center small">Pas de données</div>';
    }

    // 3. Encaissés vs Pénalités Chart (Bar)
    const encaissesData = @json($encaissesVsPenalitesData);
    const encaissesOptions = {
        series: [{
            name: 'Montant',
            data: encaissesData.series
        }],
        chart: {
            type: 'bar',
            height: 250,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                distributed: true,
            }
        },
        colors: ['#10b981', '#ef4444'], // Green for revenue, Red for penalties
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toLocaleString('fr-FR') + " DH";
            },
            style: {
                fontSize: '12px',
            }
        },
        xaxis: {
            categories: encaissesData.labels,
            labels: {
                show: false
            }
        },
        legend: { show: false },
        grid: {
            xaxis: {
                lines: { show: true }
            },
            yaxis: {
                lines: { show: false }
            }
        }
    };
    new ApexCharts(document.querySelector("#encaissesChart"), encaissesOptions).render();
});
</script>
