<?php
namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\TitreRecette;
use App\Services\PaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function __construct(private readonly PaiementService $paiementService)
    {
    }

    public function index(Request $request): View
    {
        $query = Paiement::query()->with(['titreRecette.agriculteur', 'quittance']);

        if ($request->filled('titre_recette_id')) {
            $query->where('titre_recette_id', $request->integer('titre_recette_id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $terms = explode(' ', trim($search));
            foreach ($terms as $term) {
                if (!empty($term)) {
                    $query->where(function($q) use ($term) {
                        $q->where('reference', 'like', '%' . $term . '%')
                          ->orWhereHas('titreRecette', function($q) use ($term) {
                              $q->where('numero', 'like', '%' . $term . '%')
                                ->orWhereHas('agriculteur', function($q) use ($term) {
                                    $q->where('nom', 'like', '%' . $term . '%')
                                      ->orWhere('prenom', 'like', '%' . $term . '%')
                                      ->orWhere('cin', 'like', '%' . $term . '%');
                                });
                          });
                    });
                }
            }
        }

        $paiements = $query->latest('date_paiement')->paginate(20)->appends($request->all());
        $titresRecettes = $this->loadTitresRecettesForForms();

        foreach ($paiements as $paiement) {
            $paiement->titreRecette?->calculatePenalty();
        }

        return view('paiements.index', compact('paiements', 'titresRecettes'));
    }

    public function create(): View
    {
        $titresRecettes = $this->loadTitresRecettesForForms();
        return view('paiements.create', compact('titresRecettes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $titreRecette = TitreRecette::query()->findOrFail($request->titre_recette_id);
        $titreRecette->calculatePenalty();
        $soldeRestant = (float) $titreRecette->solde_restant;
        
        $data = $request->validate([
            'reference' => ['nullable', 'string', 'max:255', 'unique:tenant.paiements,reference'],
            'titre_recette_id' => ['required', 'exists:tenant.titres_recettes,id'],
            'montant' => ['required', 'numeric', 'min:0.01', 'max:' . $soldeRestant],
            'date_paiement' => ['required', 'date'],
            'type_paiement' => ['required', 'in:ESPECES,CHEQUE,VIREMENT'],
            'statut' => ['required', 'string', 'max:32'],
            'numero_cheque' => ['nullable', 'string', 'max:64'],
        ], [
            'montant.max' => 'Le montant ne peut pas dépasser le solde restant de ' . number_format($soldeRestant, 2, ',', ' ') . ' DH.',
        ]);

        $this->paiementService->create($data);

        return redirect()->route('paiements.index')->with('status', 'Paiement enregistré et quittance générée.');
    }

    public function show(Paiement $paiement): View
    {
        $paiement->load(['titreRecette.agriculteur', 'quittance']);
        $paiement->titreRecette?->calculatePenalty();
        return view('paiements.show', compact('paiement'));
    }

    public function edit(Paiement $paiement): View
    {
        $titresRecettes = $this->loadTitresRecettesForForms();
        return view('paiements.edit', compact('paiement', 'titresRecettes'));
    }

    public function update(Request $request, Paiement $paiement): RedirectResponse
    {
        $titreRecette = TitreRecette::query()->findOrFail($request->titre_recette_id);
        $titreRecette->calculatePenalty();
        $soldeRestant = (float) $titreRecette->solde_restant;
        
        // If changing titre, use the new titre's solde. If same titre, add back old payment
        if ($request->titre_recette_id == $paiement->titre_recette_id) {
            $soldeRestant += (float) $paiement->montant;
        }
        
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:255', 'unique:tenant.paiements,reference,'.$paiement->id],
            'titre_recette_id' => ['required', 'exists:tenant.titres_recettes,id'],
            'montant' => ['required', 'numeric', 'min:0.01', 'max:' . $soldeRestant],
            'date_paiement' => ['required', 'date'],
            'type_paiement' => ['required', 'in:ESPECES,CHEQUE,VIREMENT'],
            'statut' => ['required', 'string', 'max:32'],
            'numero_cheque' => ['nullable', 'string', 'max:64'],
        ], [
            'montant.max' => 'Le montant ne peut pas dépasser le solde restant de ' . number_format($soldeRestant, 2, ',', ' ') . ' DH.',
        ]);

        $this->paiementService->update($paiement, $data);

        return redirect()->route('paiements.index')->with('status', 'Paiement mis à jour.');
    }

    public function destroy(Paiement $paiement): RedirectResponse
    {
        $this->paiementService->delete($paiement);

        return redirect()->route('paiements.index')->with('status', 'Paiement supprimé.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, TitreRecette>
     */
    private function loadTitresRecettesForForms()
    {
        $titresRecettes = TitreRecette::query()
            ->with('agriculteur')
            ->where('statut', '!=', 'SOLDE')
            ->orderByDesc('date_emission')
            ->get();

        TitreRecette::refreshPenaltiesFor($titresRecettes);

        return $titresRecettes;
    }
}