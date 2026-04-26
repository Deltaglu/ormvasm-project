<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'libelle',
        'tarif',
        'unite',
        'description',
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($prestation) {
            if (empty($prestation->code)) {
                $prestation->code = self::generateCode();
            }
        });
    }

    private static function generateCode(): string
    {
        $prefix = 'PRE';
        $latest = self::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}