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
            'total_agri' => Agriculteur::count(),
            'total_encaissé' => Paiement::sum('montant'),
            'titres_en_retard' => TitreRecette::where('statut', 'en retard')->count(),
        ];

        $topAgris = Agriculteur::withSum('titresRecettes', 'solde_restant')
            ->orderByDesc('titres_recettes_sum_solde_restant')
            ->limit(10)
            ->get(['prenom', 'nom', 'cin'])
            ->map(fn($a) => "{$a->prenom} {$a->nom} (CIN: {$a->cin}, Dette: " . number_format($a->titres_recettes_sum_solde_restant, 2) . " DH)")
            ->join(", ");

        return "Stats: " . json_encode($stats) . ". Agriculteurs: {$topAgris}.";
    }

    private function askLocalBrain($q): JsonResponse
    {
        $q = strtolower($q);
        if (str_contains($q, 'combien') || str_contains($q, 'how many')) {
            if (str_contains($q, 'agri')) return response()->json(['message' => "Nous avons **" . Agriculteur::count() . " agriculteurs**."]);
            if (str_contains($q, 'paiement')) return response()->json(['message' => "Il y a **" . Paiement::count() . " paiements**."]);
        }
        
        return response()->json([
            'message' => "💡 *L'intelligence Gemini est inactive. Vérifiez votre GEMINI_API_KEY dans le fichier .env et redémarrez le serveur.*"
        ]);
    }
}
