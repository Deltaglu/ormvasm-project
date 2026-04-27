<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Agriculteur extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'type',
        'parent_id',
        'nom',
        'prenom',
        'cin',
        'telephone',
        'adresse',
        'email',
    ];

    public function parent()
    {
        return $this->belongsTo(Agriculteur::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Agriculteur::class, 'parent_id');
    }

    public function titresRecettes(): HasMany
    {
        return $this->hasMany(TitreRecette::class, 'agriculteur_id');
    }

    /**
     * Paiements de cet agriculteur (via ses titres de recette).
     */
    public function paiements(): HasManyThrough
    {
        return $this->hasManyThrough(
            Paiement::class,
            TitreRecette::class,
            'agriculteur_id',
            'titre_recette_id',
            'id',
            'id'
        );
    }
}
