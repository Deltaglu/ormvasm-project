<?php
namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\TitreRecette;
use App\Models\User;
use Illuminate\View\View;

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

        return view('dashboard', compact('stats', 'recentActivity'));
    }
}
