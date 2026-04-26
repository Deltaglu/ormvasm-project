<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class TitreRecette extends Model
{
    protected $table = 'titres_recettes';
    protected $connection = 'tenant';

    protected $fillable = [
        'numero',
        'date_emission',
        'date_echeance',
        'montant_total',
        'montant_paye',
        'solde_restant',
        'montant_penalite',
        'penalite_appliquee',
        'statut',
        'objet',
        'agriculteur_id',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'montant_total' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'solde_restant' => 'decimal:2',
        'montant_penalite' => 'decimal:2',
        'penalite_appliquee' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($titre) {
            if (empty($titre->numero)) {
                $titre->numero = self::generateNumero();
            }
        });
    }

    private static function generateNumero(): string
    {
        $prefix = 'TR';
        $year = date('Y');
        $latest = self::where('numero', 'like', $prefix . $year . '%')
            ->orderBy('numero', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->numero, strlen($prefix . $year));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function agriculteur(): BelongsTo
    {
        return $this->belongsTo(Agriculteur::class);
    }

    
    public function prestations(): BelongsToMany
    {
        return $this->belongsToMany(Prestation::class, 'titre_prestations')
            ->withPivot('quantity', 'unit_price', 'total')
            ->withTimestamps();
    }
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'titre_recette_id');
    }

    /**
     * Montant total du titre + pénalité de retard (affichage).
     */
    public function getMontantTotalAvecPenaliteAttribute(): float
    {
        return round((float) $this->montant_total + (float) $this->montant_penalite, 2);
    }

    /**
     * Solde restant + pénalité (montant à recouvrer incluant pénalité).
     */
    public function getSoldeAvecPenaliteAttribute(): float
    {
        return round((float) $this->solde_restant, 2);
    }

    /**
     * Recalcule et enregistre la pénalité si l'échéance est dépassée.
     *
     * Les deux pénalités s'appliquent simultanément :
     * - Mensuelle récurrente : solde × taux_mensuel × mois_retard
     * - Unique après 2 mois  : solde × taux_unique (une seule fois, si ≥ 2 mois)
     */
    public function calculatePenalty(): bool
    {
        if ($this->date_echeance === null) {
            return $this->resetPenaltyIfNeeded();
        }

        $today = Carbon::today();
        $echeance = $this->date_echeance instanceof Carbon
            ? $this->date_echeance->copy()->startOfDay()
            : Carbon::parse($this->date_echeance)->startOfDay();

        if ($today->lte($echeance)) {
            return $this->resetPenaltyIfNeeded();
        }

        $solde = (float) $this->montant_total;

        // Calculate months overdue (number of 30-day periods)
        $monthsLate = $echeance->diffInMonths($today);
        if ($monthsLate < 1) {
            $monthsLate = 1;
        }

        $penalty = 0.0;

        // 1. Monthly recurring: always applies when late
        $monthlyRate = Setting::monthlyPenaltyRate();
        if ($monthlyRate > 0 && $solde > 0) {
            $penalty += round($solde * ($monthlyRate / 100.0) * $monthsLate, 2);
        }

        // 2. One-time: applies once only if delay >= 2 months
        if ($monthsLate >= 2) {
            $oneTimeRate = Setting::oneTimePenaltyRate();
            if ($oneTimeRate > 0 && $solde > 0) {
                $penalty += round($solde * ($oneTimeRate / 100.0), 2);
            }
        }

        $penalty = round($penalty, 2);
        $applied = $penalty > 0.0;

        $dirty = abs((float) $this->montant_penalite - $penalty) > 0.00001
            || (bool) $this->penalite_appliquee !== $applied;

        if ($dirty) {
            $this->forceFill([
                'montant_penalite' => $penalty,
                'penalite_appliquee' => $applied,
            ])->save();
            $this->refresh();

            return true;
        }

        return false;
    }

    /**
     * Met à jour les pénalités pour une liste de titres (pagination, collection, relations).
     */
    public static function refreshPenaltiesFor(iterable $titres): void
    {
        foreach ($titres as $titre) {
            if ($titre instanceof self) {
                $titre->calculatePenalty();
            }
        }
    }

    /**
     * @param  Collection<int, TitreRecette>  $collection
     */
    public static function refreshPenaltiesInCollection(Collection $collection): void
    {
        $collection->each(fn (self $titre) => $titre->calculatePenalty());
    }

    private function resetPenaltyIfNeeded(): bool
    {
        if (abs((float) $this->montant_penalite) > 0.00001 || $this->penalite_appliquee) {
            $this->forceFill([
                'montant_penalite' => 0,
                'penalite_appliquee' => false,
            ])->save();
            $this->refresh();

            return true;
        }

        return false;
    }
}