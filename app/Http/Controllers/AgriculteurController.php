<?php
namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\TitreRecette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgriculteurController extends Controller
{
    public function index(): View
    {
        $agriculteurs = Agriculteur::withTrashed()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
        
        return view('agriculteurs.index', compact('agriculteurs'));
    }

    public function create(): View
    {
        return view('agriculteurs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'cin' => ['required', 'string', 'max:32', 'unique:tenant.agriculteurs,cin'],
            'telephone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        Agriculteur::query()->create($data);
        
        return redirect()->route('agriculteurs.index')->with('status', 'Agriculteur cree.');
    }

    public function show(Agriculteur $agriculteur): View
    {
        $agriculteur->load([
            'titresRecettes' => function($q) { $q->withTrashed(); },
            'titresRecettes.paiements' => function($q) { $q->withTrashed(); },
            'titresRecettes.paiements.quittance' => function($q) { $q->withTrashed(); }
        ]);
        
        TitreRecette::refreshPenaltiesFor($agriculteur->titresRecettes);
        
        return view('agriculteurs.show', compact('agriculteur'));
    }

    public function edit(Agriculteur $agriculteur): View
    {
        return view('agriculteurs.edit', compact('agriculteur'));
    }

    public function update(Request $request, Agriculteur $agriculteur): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'cin' => ['required', 'string', 'max:32', 'unique:tenant.agriculteurs,cin,'.$agriculteur->id],
            'telephone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ]);

        $agriculteur->update($data);
        
        return redirect()->route('agriculteurs.index')->with('status', 'Agriculteur mis a jour.');
    }

    public function destroy(Agriculteur $agriculteur): RedirectResponse
    {
        $agriculteur->delete();
        
        return redirect()->route('agriculteurs.index')->with('status', 'Agriculteur supprime.');
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $terms = explode(' ', trim($query));
        $resultsQuery = Agriculteur::withTrashed();

        foreach ($terms as $term) {
            if (!empty($term)) {
                $resultsQuery->where(function($q) use ($term) {
                    $q->where('nom', 'like', '%' . $term . '%')
                      ->orWhere('prenom', 'like', '%' . $term . '%')
                      ->orWhere('cin', 'like', '%' . $term . '%')
                      ->orWhere('email', 'like', '%' . $term . '%');
                });
            }
        }

        $results = $resultsQuery->limit(10)->get(['id', 'nom', 'prenom', 'cin', 'deleted_at']);

        return response()->json($results);
    }

    public function releve(Agriculteur $agriculteur)
    {
        $agriculteur->load([
            'titresRecettes' => function($q) { $q->withTrashed(); },
            'titresRecettes.paiements' => function($q) { $q->withTrashed(); },
            'titresRecettes.paiements.quittance' => function($q) { $q->withTrashed(); }
        ]);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.releve', compact('agriculteur'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Releve_{$agriculteur->nom}_{$agriculteur->cin}.pdf");
    }
}
