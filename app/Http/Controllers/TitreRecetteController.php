<?php
namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\Prestation;
use App\Models\TitreRecette;
use App\Services\PaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TitreRecetteController extends Controller
{
    public function __construct(private readonly PaiementService $paiementService)
    {
    }

    public function index(): View
    {
        $titresRecettes = TitreRecette::withTrashed()
            ->with('agriculteur')
            ->orderByRaw('deleted_at IS NOT NULL')
            ->latest('date_emission')
            ->get();

        $titresRecettes->each(function ($titre) {
            $titre->calculatePenalty();
        });

        return view('titres_recettes.index', compact('titresRecettes'));
    }

    public function create(): View
    {
        $agriculteurs = Agriculteur::whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get()
            ->groupBy('type');
        $prestations = Prestation::query()->orderBy('libelle')->get();
        return view('titres_recettes.create', compact('agriculteurs', 'prestations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'numero' => ['nullable', 'string', 'max:255', 'unique:tenant.titres_recettes,numero'],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date'],
            'objet' => ['nullable', 'string'],
            'agriculteur_id' => ['required', 'exists:tenant.agriculteurs,id'],
            'prestations' => ['required', 'array', 'min:1'],
            'prestations.*.prestation_id' => ['required', 'exists:tenant.prestations,id'],
            'prestations.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'prestations.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['date_echeance'] = $request->filled('date_echeance') ? $data['date_echeance'] : null;

        $montantTotal = 0;
        $prestationData = [];

        foreach ($data['prestations'] as $item) {
            $prestation = Prestation::findOrFail($item['prestation_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $lineTotal = $quantity * $unitPrice;
            $montantTotal += $lineTotal;

            $prestationData[$prestation->id] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
            ];
        }

        $data['montant_total'] = $montantTotal;
        $data['montant_paye'] = 0;
        $data['solde_restant'] = $montantTotal;
        $data['statut'] = 'PARTIEL';
        $data['montant_penalite'] = 0;
        $data['penalite_appliquee'] = false;

        unset($data['prestations']);

        $titre = TitreRecette::query()->create($data);
        $titre->prestations()->sync($prestationData);
        $titre->calculatePenalty();

        return redirect()->route('titres-recettes.index')->with('status', 'Titre de recette cree.');
    }

    public function show(TitreRecette $titres_recette): View
    {
        $titres_recette->calculatePenalty();
        $titres_recette->load(['agriculteur', 'paiements.quittance']);
        return view('titres_recettes.show', ['titreRecette' => $titres_recette]);
    }

    public function edit(TitreRecette $titres_recette): View
    {
        $agriculteurs = Agriculteur::whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get()
            ->groupBy('type');
        $prestations = Prestation::query()->orderBy('libelle')->get();
        $titres_recette->load('prestations');
        return view('titres_recettes.edit', [
            'titreRecette' => $titres_recette,
            'agriculteurs' => $agriculteurs,
            'prestations' => $prestations,
        ]);
    }

    public function update(Request $request, TitreRecette $titres_recette): RedirectResponse
    {
        $data = $request->validate([
            'numero' => ['required', 'string', 'max:255', 'unique:tenant.titres_recettes,numero,'.$titres_recette->id],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date'],
            'objet' => ['nullable', 'string'],
            'agriculteur_id' => ['required', 'exists:tenant.agriculteurs,id'],
            'prestations' => ['required', 'array', 'min:1'],
            'prestations.*.prestation_id' => ['required', 'exists:tenant.prestations,id'],
            'prestations.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'prestations.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['date_echeance'] = $request->filled('date_echeance') ? $data['date_echeance'] : null;

        $montantTotal = 0;
        $prestationData = [];

        foreach ($data['prestations'] as $item) {
            $prestation = Prestation::findOrFail($item['prestation_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $lineTotal = $quantity * $unitPrice;
            $montantTotal += $lineTotal;

            $prestationData[$prestation->id] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
            ];
        }

        $data['montant_total'] = $montantTotal;
        $difference = $montantTotal - (float) $titres_recette->montant_total;
        $data['solde_restant'] = max((float) $titres_recette->solde_restant + $difference, 0);
        $data['statut'] = $data['solde_restant'] == 0.0 ? 'SOLDE' : 'PARTIEL';

        unset($data['prestations']);

        $titres_recette->update($data);
        $titres_recette->prestations()->sync($prestationData);
        $titres_recette->refresh()->calculatePenalty();

        // Regenerate quittances for all payments associated with this titre
        $titres_recette->load('paiements.quittance');
        foreach ($titres_recette->paiements as $paiement) {
            if ($paiement->quittance) {
                $this->paiementService->regenerateQuittance($paiement->load('titreRecette.agriculteur'));
            }
        }

        return redirect()->route('titres-recettes.index')->with('status', 'Titre de recette mis a jour.');
    }

    public function destroy(TitreRecette $titres_recette): RedirectResponse
    {
        if ($titres_recette->paiements()->exists()) {
            return redirect()->route('titres-recettes.index')->withErrors([
                'general' => 'Impossible de supprimer un titre ayant des paiements.',
            ]);
        }

        $titres_recette->delete();

        return redirect()->route('titres-recettes.index')->with('status', 'Titre de recette supprime.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $terms = explode(' ', trim($query));
        $resultsQuery = TitreRecette::query()->with('agriculteur');

        foreach ($terms as $term) {
            if (!empty($term)) {
                $resultsQuery->where(function($q) use ($term) {
                    $q->where('numero', 'like', '%' . $term . '%')
                      ->orWhereHas('agriculteur', function($q) use ($term) {
                          $q->where('nom', 'like', '%' . $term . '%')
                            ->orWhere('prenom', 'like', '%' . $term . '%');
                      });
                });
            }
        }

        $results = $resultsQuery->limit(10)->get(['id', 'numero', 'agriculteur_id']);

        return response()->json($results);
    }
}