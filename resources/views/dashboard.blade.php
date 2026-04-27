@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))

@section('content')
<x-page-header title="Tableau de bord" subtitle="Vue d'ensemble de l'activité et des indicateurs clés." />

{{-- KPI Cards --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
    <div class="col">
        <div class="ormsa-stat">
            <div class="ormsa-stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="ormsa-stat-label">Clients</div>
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
    <div class="col-xl-6">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-graph-up-arrow"></i> Recouvrement (12 derniers mois)
            </div>
            <div class="p-3">
                <div id="revenueChart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-speedometer2"></i> Taux de Recouvrement
            </div>
            <div class="p-3 d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 250px;">
                <div id="recoveryGauge" class="w-100"></div>
                <div class="text-center mt-2 small text-muted">Total collecté vs Émis</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-pie-chart-fill"></i> Prestations (Top 5)
            </div>
            <div class="p-3 d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                <div id="prestationsChart" class="w-100"></div>
            </div>
        </div>
    </div>
</div>

{{-- Secondary Stats & Recent Activity --}}
<div class="row g-4 mb-4">
    <div class="col-xl-4">
        <div class="ormsa-surface h-100">
            <div class="ormsa-surface-header">
                <i class="bi bi-bar-chart-fill"></i> Encaissé vs Pénalités
            </div>
            <div class="p-3 d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                <div id="encaissesChart" class="w-100"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="ormsa-surface ormsa-table-wrap h-100">
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
                            <th>Client</th>
                            <th>Titre</th>
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
                            <td>{{ $t?->agriculteur?->type === 'society' ? $t?->agriculteur?->nom : ($t?->agriculteur?->prenom . ' ' . $t?->agriculteur?->nom) }}</td>
                            <td><code class="small">{{ $t?->numero }}</code></td>
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
                            <td colspan="6">
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
    </div>
</div>
@endsection

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
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

    // 4. Recovery Gauge (Circular)
    const recoveryOptions = {
        series: [{{ $recoveryRate }}],
        chart: {
            type: 'radialBar',
            height: 250,
            sparkline: { enabled: true }
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                hollow: { size: '60%' },
                track: {
                    background: isDark ? '#1e293b' : '#f1f5f9',
                    strokeWidth: '67%',
                },
                dataLabels: {
                    name: { show: false },
                    value: {
                        offsetY: 10,
                        fontSize: '24px',
                        fontWeight: '700',
                        color: isDark ? '#f8fafc' : '#1e293b',
                        formatter: val => val + "%"
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                gradientToColors: ['#10b981'],
                stops: [0, 100]
            }
        },
        stroke: { lineCap: 'round' },
        labels: ['Recouvré'],
    };
    new ApexCharts(document.querySelector("#recoveryGauge"), recoveryOptions).render();
});
</script>
