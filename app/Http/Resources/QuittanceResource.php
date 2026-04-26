<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuittanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'paiement' => new PaiementResource($this->whenLoaded('paiement')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
