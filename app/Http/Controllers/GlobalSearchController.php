<?php

namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\TitreRecette;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q');
        if (strlen($q) < 2) return response()->json([]);

        $results = [];

        // 1. Agriculteurs
        $agris = Agriculteur::where('nom', 'LIKE', "%{$q}%")
            ->orWhere('prenom', 'LIKE', "%{$q}%")
            ->orWhere('cin', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get();
        foreach ($agris as $a) {
            $results[] = [
                'type' => 'Agriculteur',
                'icon' => 'bi-people',
                'title' => $a->prenom . ' ' . $a->nom,
                'subtitle' => 'CIN: ' . $a->cin,
                'url' => route('agriculteurs.index') . '?search=' . $a->cin
            ];
        }

        // 2. Paiements
        $paiements = Paiement::where('reference', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get();
        foreach ($paiements as $p) {
            $results[] = [
                'type' => 'Paiement',
                'icon' => 'bi-cash-stack',
                'title' => 'Réf: ' . $p->reference,
                'subtitle' => 'Montant: ' . number_format($p->montant, 2) . ' DH',
                'url' => route('paiements.index') . '?search=' . $p->reference
            ];
        }

        // 3. Titres
        $titres = TitreRecette::where('numero', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get();
        foreach ($titres as $t) {
            $results[] = [
                'type' => 'Titre de Recette',
                'icon' => 'bi-receipt',
                'title' => 'TR: ' . $t->numero,
                'subtitle' => 'Date: ' . ($t->date_emission ? $t->date_emission->format('d/m/Y') : '—'),
                'url' => route('titres-recettes.index') . '?search=' . $t->numero
            ];
        }

        return response()->json($results);
    }
}
