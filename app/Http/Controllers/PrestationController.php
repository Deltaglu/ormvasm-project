<?php
namespace App\Http\Controllers;

use App\Models\Prestation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrestationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Prestation::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $terms = explode(' ', trim($search));
            foreach ($terms as $term) {
                if (!empty($term)) {
                    $query->where(function($q) use ($term) {
                        $q->where('code', 'like', '%' . $term . '%')
                          ->orWhere('libelle', 'like', '%' . $term . '%');
                    });
                }
            }
        }
        
        $prestations = $query->orderBy('code')->paginate(15)->appends($request->all());
        return view('prestations.index', compact('prestations'));
    }

    public function create(): View
    {
        return view('prestations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:255', 'unique:tenant.prestations,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'tarif' => ['required', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        Prestation::query()->create($data);

        return redirect()->route('prestations.index')->with('status', 'Prestation creee.');
    }

    public function show(Prestation $prestation): View
    {
        return view('prestations.show', compact('prestation'));
    }

    public function edit(Prestation $prestation): View
    {
        return view('prestations.edit', compact('prestation'));
    }

    public function update(Request $request, Prestation $prestation): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:tenant.prestations,code,'.$prestation->id],
            'libelle' => ['required', 'string', 'max:255'],
            'tarif' => ['required', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
        ]);

        $prestation->update($data);

        return redirect()->route('prestations.index')->with('status', 'Prestation mise a jour.');
    }

    public function destroy(Prestation $prestation): RedirectResponse
    {
        $prestation->delete();

        return redirect()->route('prestations.index')->with('status', 'Prestation supprimee.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $terms = explode(' ', trim($query));
        $resultsQuery = Prestation::query();

        foreach ($terms as $term) {
            if (!empty($term)) {
                $resultsQuery->where(function($q) use ($term) {
                    $q->where('code', 'like', '%' . $term . '%')
                      ->orWhere('libelle', 'like', '%' . $term . '%');
                });
            }
        }

        $results = $resultsQuery->limit(10)->get(['id', 'code', 'libelle']);

        return response()->json($results);
    }
}