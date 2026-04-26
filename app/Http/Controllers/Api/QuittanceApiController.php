<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuittanceResource;
use App\Models\Quittance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuittanceApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return QuittanceResource::collection(
            Quittance::query()->with(['paiement.agriculteur', 'paiement.campagne'])->latest()->paginate(50)
        );
    }

    public function show(Quittance $quittance): QuittanceResource
    {
        return new QuittanceResource($quittance->load(['paiement.agriculteur', 'paiement.campagne']));
    }

    public function pdf(Quittance $quittance)
    {
        $quittance->load(['paiement.agriculteur', 'paiement.campagne']);
        $pdf = Pdf::loadView('quittances.pdf', ['quittance' => $quittance]);

        return $pdf->download('quittance-'.$quittance->numero.'.pdf');
    }
}
