<?php
namespace App\Http\Controllers;

use App\Models\Campagne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampagneController extends Controller
{
    public function index(): View
    {
        $campagnes = Campagne::query()->orderByDesc('annee')->paginate(15);

        return view('campagnes.index', compact('campagnes'));
    }

    public function create(): View
    {
        return view('campagnes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'annee' => ['required', 'integer', 'min:1900', 'max:2100', 'unique:tenant.campagnes,annee'],
            'description' => ['nullable', 'string'],
        ]);

        Campagne::query()->create($data);

        return redirect()->route('campagnes.index')->with('status', 'Campagne créée.');
    }

    public function show(Campagne $campagne): View
    {
        $campagne->load(['paiements.agriculteur', 'paiements.quittance']);

        return view('campagnes.show', compact('campagne'));
    }

    public function edit(Campagne $campagne): View
    {
        return view('campagnes.edit', compact('campagne'));
    }

    public function update(Request $request, Campagne $campagne): RedirectResponse
    {
        $data = $request->validate([
            'annee' => ['required', 'integer', 'min:1900', 'max:2100', 'unique:tenant.campagnes,annee,'.$campagne->id],
            'description' => ['nullable', 'string'],
        ]);

        $campagne->update($data);

        return redirect()->route('campagnes.index')->with('status', 'Campagne mise à jour.');
    }

    public function destroy(Campagne $campagne): RedirectResponse
    {
        $campagne->delete();

        return redirect()->route('campagnes.index')->with('status', 'Campagne supprimée.');
    }
}
