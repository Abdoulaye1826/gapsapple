<?php

namespace App\Enums;

enum ImeiStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Reserved => 'Réservé',
            self::Sold => 'Vendu',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'bg-success',
            self::Reserved => 'bg-warning text-dark',
            self::Sold => 'bg-secondary',
        };
    }
}
