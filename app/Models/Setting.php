<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $connection = 'tenant';

    protected $table = 'settings';

    protected $fillable = [
        'monthly_penalty_rate',
        'one_time_penalty_rate',
    ];

    protected $casts = [
        'monthly_penalty_rate' => 'decimal:2',
        'one_time_penalty_rate' => 'decimal:2',
    ];

    /**
     * Configuration tenant (une seule ligne attendue).
     */
    public static function current(): self
    {
        $row = static::query()->first();
        if ($row) {
            // Auto-fill defaults if row was created before migration
            $dirty = false;
            if ($row->monthly_penalty_rate === null) {
                $row->monthly_penalty_rate = 5.00;
                $dirty = true;
            }
            if ($row->one_time_penalty_rate === null) {
                $row->one_time_penalty_rate = 2.00;
                $dirty = true;
            }
            if ($dirty) {
                $row->save();
            }
            return $row;
        }

        return static::query()->create([
            'monthly_penalty_rate' => 5.00,
            'one_time_penalty_rate' => 2.00,
        ]);
    }

    /**
     * Get monthly penalty rate
     */
    public static function monthlyPenaltyRate(): float
    {
        return (float) static::current()->monthly_penalty_rate;
    }

    /**
     * Get one-time penalty rate
     */
    public static function oneTimePenaltyRate(): float
    {
        return (float) static::current()->one_time_penalty_rate;
    }
}