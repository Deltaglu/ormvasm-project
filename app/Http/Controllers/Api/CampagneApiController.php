<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampagneResource;
use App\Models\Campagne;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampagneApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CampagneResource::collection(
            Campagne::query()->orderByDesc('annee')->paginate(50)
        );
    }

    public function store(Request $request): CampagneResource
    {
        $data = $request->validate([
            'annee' => ['required', 'integer', 'min:1900', 'max:2100', 'unique:tenant.campagnes,annee'],
            'description' => ['nullable', 'string'],
        ]);

        $campagne = Campagne::query()->create($data);

        return new CampagneResource($campagne);
    }

    public function show(Campagne $campagne): CampagneResource
    {
        return new CampagneResource($campagne->load('paiements'));
    }

    public function update(Request $request, Campagne $campagne): CampagneResource
    {
        $data = $request->validate([
            'annee' => ['sometimes', 'integer', 'min:1900', 'max:2100', 'unique:tenant.campagnes,annee,'.$campagne->id],
            'description' => ['nullable', 'string'],
        ]);

        $campagne->update($data);

        return new CampagneResource($campagne->fresh());
    }

    public function destroy(Campagne $campagne)
    {
        $campagne->delete();

        return response()->noContent();
    }
}
