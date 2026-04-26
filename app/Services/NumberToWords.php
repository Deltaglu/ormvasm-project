<?php
namespace App\Services;

class NumberToWords
{
    private static array $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
        'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    private static array $tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt',
    ];

    public static function fr(float $amount): string
    {
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);

        $result = self::convertWhole($whole);

        if ($cents > 0) {
            $result .= ' et ' . self::convertWhole($cents) . ' centimes';
        }

        return $result;
    }

    private static function convertWhole(int $n): string
    {
        if ($n === 0) {
            return 'zéro';
        }
        if ($n < 0) {
            return 'moins ' . self::convertWhole(-$n);
        }
        if ($n < 20) {
            return self::$units[$n];
        }
        if ($n < 100) {
            $ten = (int) floor($n / 10);
            $unit = $n % 10;

            // French special cases: 70-79 use soixante-dix, 80-89 quatre-vingt, 90-99 quatre-vingt-dix
            if ($ten === 7) {
                return 'soixante-' . self::$units[10 + $unit];
            }
            if ($ten === 9) {
                return 'quatre-vingt-' . self::$units[10 + $unit];
            }
            if ($ten === 8 && $unit === 0) {
                return 'quatre-vingts';
            }
            if ($unit === 0) {
                return self::$tens[$ten];
            }
            if ($ten === 8) {
                return 'quatre-vingt-' . self::$units[$unit];
            }
            if ($unit === 1) {
                return self::$tens[$ten] . ' et un';
            }
            return self::$tens[$ten] . '-' . self::$units[$unit];
        }
        if ($n < 1000) {
            $hundred = (int) floor($n / 100);
            $rest = $n % 100;
            $prefix = $hundred === 1 ? 'cent' : self::$units[$hundred] . ' cent';
            if ($rest === 0 && $hundred > 1) {
                return $prefix . 's';
            }
            if ($rest === 0) {
                return $prefix;
            }
            return $prefix . ' ' . self::convertWhole($rest);
        }
        if ($n < 1000000) {
            $thousands = (int) floor($n / 1000);
            $rest = $n % 1000;
            $prefix = $thousands === 1 ? 'mille' : self::convertWhole($thousands) . ' mille';
            if ($rest === 0) {
                return $prefix;
            }
            return $prefix . ' ' . self::convertWhole($rest);
        }
        if ($n < 1000000000) {
            $millions = (int) floor($n / 1000000);
            $rest = $n % 1000000;
            $prefix = self::convertWhole($millions) . ' million' . ($millions > 1 ? 's' : '');
            if ($rest === 0) {
                return $prefix;
            }
            return $prefix . ' ' . self::convertWhole($rest);
        }
        return (string) $n;
    }
}