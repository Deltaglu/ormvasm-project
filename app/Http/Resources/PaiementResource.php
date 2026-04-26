<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'montant' => $this->montant,
            'date_paiement' => $this->date_paiement?->format('Y-m-d'),
            'type_paiement' => $this->type_paiement,
            'agriculteur' => new AgriculteurResource($this->whenLoaded('agriculteur')),
            'campagne' => new CampagneResource($this->whenLoaded('campagne')),
            'quittance' => new QuittanceResource($this->whenLoaded('quittance')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
