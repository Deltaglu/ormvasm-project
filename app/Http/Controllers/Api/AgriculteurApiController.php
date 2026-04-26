<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgriculteurResource;
use App\Models\Agriculteur;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgriculteurApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AgriculteurResource::collection(
            Agriculteur::query()->orderBy('nom')->orderBy('prenom')->paginate(50)
        );
    }

    public function store(Request $request): AgriculteurResource
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'cin' => ['required', 'string', 'max:32', 'unique:tenant.agriculteurs,cin'],
            'telephone' => ['nullable', 'string', 'max:32'],
            'adresse' => ['nullable', 'string'],
        ]);

        $agriculteur = Agriculteur::query()->create($data);

        return new AgriculteurResource($agriculteur);
    }

    public function show(Agriculteur $agriculteur): AgriculteurResource
    {
        return new AgriculteurResource($agriculteur->load('paiements'));
    }

    public function update(Request $request, Agriculteur $agriculteur): AgriculteurResource
    {
        $data = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'cin' => ['sometimes', 'string', 'max:32', 'unique:tenant.agriculteurs,cin,'.$agriculteur->id],
            'telephone' => ['nullable', 'string', 'max:32'],
            'adresse' => ['nullable', 'string'],
        ]);

        $agriculteur->update($data);

        return new AgriculteurResource($agriculteur->fresh());
    }

    public function destroy(Agriculteur $agriculteur)
    {
        $agriculteur->delete();

        return response()->noContent();
    }
}
