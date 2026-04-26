<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaiementResource;
use App\Models\Paiement;
use App\Models\Quittance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaiementApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Paiement::query()->with(['agriculteur', 'campagne', 'quittance']);

        if ($request->filled('agriculteur_id')) {
            $query->where('agriculteur_id', $request->integer('agriculteur_id'));
        }
        if ($request->filled('campagne_id')) {
            $query->where('campagne_id', $request->integer('campagne_id'));
        }

        return PaiementResource::collection(
            $query->latest('date_paiement')->paginate(50)
        );
    }

    public function store(Request $request): PaiementResource
    {
        $data = $request->validate([
            'agriculteur_id' => ['required', 'exists:tenant.agriculteurs,id'],
            'campagne_id' => ['required', 'exists:tenant.campagnes,id'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_paiement' => ['required', 'date'],
            'type_paiement' => ['required', 'string', 'max:64'],
        ]);

        $paiement = Paiement::query()->create($data);
        Quittance::createForPaiement($paiement);

        return new PaiementResource($paiement->load(['agriculteur', 'campagne', 'quittance']));
    }

    public function show(Paiement $paiement): PaiementResource
    {
        return new PaiementResource($paiement->load(['agriculteur', 'campagne', 'quittance']));
    }

    public function update(Request $request, Paiement $paiement): PaiementResource
    {
        $data = $request->validate([
            'agriculteur_id' => ['sometimes', 'exists:tenant.agriculteurs,id'],
            'campagne_id' => ['sometimes', 'exists:tenant.campagnes,id'],
            'montant' => ['sometimes', 'numeric', 'min:0.01'],
            'date_paiement' => ['sometimes', 'date'],
            'type_paiement' => ['sometimes', 'string', 'max:64'],
        ]);

        $paiement->update($data);

        return new PaiementResource($paiement->fresh()->load(['agriculteur', 'campagne', 'quittance']));
    }

    public function destroy(Paiement $paiement)
    {
        $paiement->delete();

        return response()->noContent();
    }
}
