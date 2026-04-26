<?php
namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\TitreRecette;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        foreach (TitreRecette::query()->cursor() as $titre) {
            $titre->calculatePenalty();
        }

        $stats = [
            'total_users' => User::query()->count(),
            'total_paiements' => Paiement::query()->count(),
            'total_montant' => (float) Paiement::query()->sum('montant'),
            'total_titres' => TitreRecette::query()->count(),
            'total_agriculteurs' => Agriculteur::query()->count(),
            'total_penalites' => (float) TitreRecette::query()->sum('montant_penalite'),
        ];

        $recentActivity = Paiement::query()
            ->with(['titreRecette.agriculteur', 'quittance'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        foreach ($recentActivity as $paiement) {
            $paiement->titreRecette?->calculatePenalty();
        }

        // --- CHART DATA ---

        // 1. Revenue over the last 12 months
        $months = collect(range(11, 0))->map(function($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });
        
        $paymentsLast12Months = Paiement::query()
            ->where('date_paiement', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->get()
            ->groupBy(function($val) {
                return Carbon::parse($val->date_paiement)->format('Y-m');
            });
            
        $revenueChartData = [
            'labels' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'))->values()->toArray(),
            'series' => $months->map(fn($m) => isset($paymentsLast12Months[$m]) ? round($paymentsLast12Months[$m]->sum('montant'), 2) : 0)->values()->toArray()
        ];

        // 2. Distribution of Prestations (Top 5)
        $prestationsDistribution = DB::connection('tenant')->table('titre_prestations')
            ->join('prestations', 'titre_prestations.prestation_id', '=', 'prestations.id')
            ->select('prestations.libelle', DB::raw('SUM(titre_prestations.total) as total_amount'))
            ->groupBy('prestations.id', 'prestations.libelle')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $prestationsChartData = [
            'labels' => $prestationsDistribution->pluck('libelle')->toArray(),
            'series' => $prestationsDistribution->pluck('total_amount')->map(fn($v) => round((float)$v, 2))->toArray()
        ];

        // 3. Montant Encaissé vs Pénalités
        $encaissesVsPenalitesData = [
            'labels' => ['Encaissé', 'Pénalités'],
            'series' => [$stats['total_montant'], $stats['total_penalites']]
        ];

        // 4. Recovery Rate
        $baseIssued = TitreRecette::sum('montant_total');
        $penalties = TitreRecette::sum('montant_penalite');
        $totalIssued = ($baseIssued + $penalties) ?: 1;
        $totalCollected = $stats['total_montant'];
        $recoveryRate = round(($totalCollected / $totalIssued) * 100, 1);

        return view('dashboard', compact('stats', 'recentActivity', 'revenueChartData', 'prestationsChartData', 'encaissesVsPenalitesData', 'recoveryRate'));
    }
}
