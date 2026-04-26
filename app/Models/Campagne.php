<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campagne extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'annee',
        'description',
    ];

    protected $casts = [
        'annee' => 'integer',
    ];

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
