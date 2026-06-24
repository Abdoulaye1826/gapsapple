<?php

namespace App\Helpers;

class NumberHelper
{
    public static function toWords($number): ?string
    {
        if (!is_numeric($number)) {
            return null;
        }

        $number = (float) $number;
        if (is_infinite($number) || is_nan($number)) {
            return null;
        }

        $isNegative = $number < 0;
        $number = abs($number);
        $integer = (int) floor($number);
        $fraction = (int) round(($number - $integer) * 100);

        $words = $isNegative ? 'moins ' : '';
        $words .= static::convertInteger($integer);

        if ($fraction > 0) {
            $words .= ' virgule ' . static::convertFraction($fraction);
        }

        return $words;
    }

    protected static function convertInteger(int $number): string
    {
        if ($number === 0) {
            return 'zéro';
        }

        $parts = [];

        if ($number >= 1000000000) {
            $billions = intdiv($number, 1000000000);
            $parts[] = static::convertInteger($billions) . ' milliard' . ($billions > 1 ? 's' : '');
            $number %= 1000000000;
        }

        if ($number >= 1000000) {
            $millions = intdiv($number, 1000000);
            $parts[] = static::convertInteger($millions) . ' million' . ($millions > 1 ? 's' : '');
            $number %= 1000000;
        }

        if ($number >= 1000) {
            $thousands = intdiv($number, 1000);
            $parts[] = ($thousands === 1 ? 'mille' : static::convertInteger($thousands) . ' mille');
            $number %= 1000;
        }

        if ($number > 0) {
            $parts[] = static::convertUnderThousand($number);
        }

        return trim(implode(' ', $parts));
    }

    protected static function convertUnderThousand(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $hundreds = intdiv($number, 100);
            $remainder = $number % 100;
            $words[] = ($hundreds === 1 ? 'cent' : static::convertUnderHundred($hundreds) . ' cent') . ($remainder === 0 && $hundreds > 1 ? 's' : '');

            if ($remainder > 0) {
                $words[] = static::convertUnderHundred($remainder);
            }
        } else {
            $words[] = static::convertUnderHundred($number);
        }

        return implode(' ', $words);
    }

    protected static function convertUnderHundred(int $number): string
    {
        $units = [
            0 => 'zéro',
            1 => 'un',
            2 => 'deux',
            3 => 'trois',
            4 => 'quatre',
            5 => 'cinq',
            6 => 'six',
            7 => 'sept',
            8 => 'huit',
            9 => 'neuf',
            10 => 'dix',
            11 => 'onze',
            12 => 'douze',
            13 => 'treize',
            14 => 'quatorze',
            15 => 'quinze',
            16 => 'seize',
        ];

        if ($number <= 16) {
            return $units[$number];
        }

        if ($number < 20) {
            return 'dix-' . $units[$number - 10];
        }

        if ($number < 70) {
            $tens = intdiv($number, 10) * 10;
            $unit = $number % 10;
            $text = [20 => 'vingt', 30 => 'trente', 40 => 'quarante', 50 => 'cinquante', 60 => 'soixante'][$tens];

            if ($unit === 1) {
                return $text . ' et un';
            }

            return $text . ($unit ? '-' . $units[$unit] : '');
        }

        if ($number < 80) {
            $unit = $number - 60;
            if ($number === 71) {
                return 'soixante et onze';
            }
            return 'soixante-' . static::convertUnderHundred($unit);
        }

        $unit = $number - 80;
        $text = 'quatre-vingt';

        if ($unit === 0) {
            return 'quatre-vingts';
        }

        if ($unit === 1) {
            return $text . '-un';
        }

        return $text . '-' . static::convertUnderHundred($unit);
    }

    protected static function convertFraction(int $number): string
    {
        if ($number === 0) {
            return 'zéro';
        }

        return static::convertInteger($number);
    }
}
