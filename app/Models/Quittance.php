<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Quittance extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'paiement_id',
        'numero',
        'date_generation',
        'chemin_pdf',
        'montant_paye',
    ];

    protected $connection = 'tenant';

    protected $casts = [
        'date_generation' => 'datetime',
        'montant_paye' => 'decimal:2',
    ];

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }
}
