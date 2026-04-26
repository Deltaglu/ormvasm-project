<?php

namespace App\Http\Controllers;

use App\Models\Agriculteur;
use App\Models\Paiement;
use App\Models\Prestation;
use App\Models\TitreRecette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(): View
    {
        $trashedAgriculteurs = Agriculteur::onlyTrashed()->get();
        $trashedPaiements = Paiement::onlyTrashed()->with('titreRecette')->get();
        $trashedTitres = TitreRecette::onlyTrashed()->get();
        $trashedPrestations = Prestation::onlyTrashed()->get();

        return view('trash.index', compact(
            'trashedAgriculteurs',
            'trashedPaiements',
            'trashedTitres',
            'trashedPrestations'
        ));
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        $model = $this->getModelByType($type, $id, true);
        $model->restore();

        // Cascading restore for Paiement -> Quittance
        if ($type === 'paiement' && method_exists($model, 'quittance')) {
            $model->quittance()->withTrashed()->restore();
        }

        return redirect()->back()->with('status', "L'élément a été restauré avec succès.");
    }

    public function forceDelete(string $type, int $id): RedirectResponse
    {
        $model = $this->getModelByType($type, $id, true);
        $model->forceDelete();

        return redirect()->route('trash.index')->with('status', "L'élément a été définitivement supprimé.");
    }

    private function getModelByType(string $type, int $id, bool $onlyTrashed = false)
    {
        $class = match($type) {
            'agriculteur' => Agriculteur::class,
            'paiement' => Paiement::class,
            'titre' => TitreRecette::class,
            'prestation' => Prestation::class,
            default => abort(404)
        };

        $query = $onlyTrashed ? $class::onlyTrashed() : $class::query();
        return $query->findOrFail($id);
    }
}
