<?php

namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\TitreRecette;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Gemini\Laravel\Facades\Gemini;

class AiAssistantController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        if (empty($q)) return response()->json(['message' => 'Comment puis-je vous aider ?']);

        // Check for Gemini Key directly from ENV or Config
        $geminiKey = env('GEMINI_API_KEY') ?: config('gemini.api_key');
        
        if ($geminiKey && strlen($geminiKey) > 10) {
            config(['gemini.api_key' => $geminiKey]); // Force it at runtime
            try {
                return $this->askGemini($q);
            } catch (\Exception $e) {
                // If Gemini fails (e.g. invalid key), we'll show the error
                return response()->json(['message' => "Erreur Gemini: " . $e->getMessage()]);
            }
        }

        return $this->askLocalBrain($q);
    }

    private function askGemini($q): JsonResponse
    {
        $context = $this->buildContext();
        
        $prompt = "Tu es l'Expert Financier ORMSA. Ta mission est de fournir des réponses ultra-précises, concises et professionnelles.
                   RÈGLES :
                   1. Pas de formules de politesse inutiles (évite les 'Bonjour', 'J'espère que vous allez bien').
                   2. Utilise le Gras pour les chiffres et les noms importants.
                   3. Sois pro-actif : si on te demande un chiffre, donne aussi une brève analyse (ex: tendance ou alerte).
                   4. Utilise des listes à puces pour la clarté.
                   
                   DONNÉES SYSTÈME :
                   " . $context . "
                   
                   QUESTION : " . $q;

        $result = Gemini::generativeModel('gemini-2.5-flash-lite')->generateContent($prompt);

        return response()->json([
            'message' => $result->text(),
            'is_ai' => true
        ]);
    }

    private function buildContext(): string
    {
        $stats = [
            'total_agri' => Agriculteur::whereNull('parent_id')->count(),
            'total_individuals' => Agriculteur::whereNull('parent_id')->where('type', 'individual')->count(),
            'total_societies' => Agriculteur::whereNull('parent_id')->where('type', 'society')->count(),
            'total_encaissé' => Paiement::sum('montant'),
            'titres_en_retard' => TitreRecette::where('statut', 'en retard')->count(),
        ];

        $agriDetails = Agriculteur::whereNull('parent_id')
            ->with(['titresRecettes' => function($q) {
                $q->select('id', 'agriculteur_id', 'montant_total', 'montant_penalite', 'montant_paye', 'solde_restant', 'statut', 'date_echeance');
            }])
            ->get(['id', 'prenom', 'nom', 'cin', 'type'])
            ->map(function($a) {
                $trDetails = $a->titresRecettes->map(function($tr) {
                    return "TR#{$tr->id}: {$tr->montant_penalite} DH penalty, {$tr->solde_restant} DH solde";
                })->join(", ");
                $totalPenalite = $a->titresRecettes->sum('montant_penalite');
                $totalSolde = $a->titresRecettes->sum('solde_restant');
                $enRetard = $a->titresRecettes->where('statut', 'en retard')->count();
                return "{$a->prenom} {$a->nom} ({$a->type}): [{$trDetails}] Total Pénalités: " . number_format($totalPenalite, 2) . " DH, Dette: " . number_format($totalSolde, 2) . " DH, TR en retard: {$enRetard}";
            })
            ->join(" | ");

        return "Stats: " . json_encode($stats) . ". Détails agriculteurs: {$agriDetails}.";
    }

    private function askLocalBrain($q): JsonResponse
    {
        $q = strtolower($q);
        if (str_contains($q, 'combien') || str_contains($q, 'how many')) {
            if (str_contains($q, 'agri')) {
                $total = Agriculteur::whereNull('parent_id')->count();
                $individuals = Agriculteur::whereNull('parent_id')->where('type', 'individual')->count();
                $societies = Agriculteur::whereNull('parent_id')->where('type', 'society')->count();
                return response()->json(['message' => "Nous avons **{$total} agriculteurs** ({$individuals} particuliers, {$societies} sociétés)."]);
            }
            if (str_contains($q, 'paiement')) return response()->json(['message' => "Il y a **" . Paiement::count() . " paiements**."]);
        }
        
        return response()->json([
            'message' => "💡 *L'intelligence Gemini est inactive. Vérifiez votre GEMINI_API_KEY dans le fichier .env et redémarrez le serveur.*"
        ]);
    }
}
