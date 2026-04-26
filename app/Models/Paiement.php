<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Paiement extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'reference',
        'date_paiement',
        'montant',
        'type_paiement',
        'statut',
        'numero_cheque',
        'titre_recette_id',
    ];

    protected $connection = 'tenant';

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($paiement) {
            if (empty($paiement->reference)) {
                $paiement->reference = self::generateReference();
            }
        });
    }

    private static function generateReference(): string
    {
        $prefix = 'PAI';
        $year = date('Y');
        $month = date('m');
        $latest = self::withTrashed()->where('reference', 'like', $prefix . $year . $month . '%')
            ->orderBy('reference', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->reference, strlen($prefix . $year . $month));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function titreRecette(): BelongsTo
    {
        return $this->belongsTo(TitreRecette::class, 'titre_recette_id');
    }

    public function quittance(): HasOne
    {
        return $this->hasOne(Quittance::class);
    }
}