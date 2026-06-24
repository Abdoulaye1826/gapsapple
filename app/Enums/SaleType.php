<?php

namespace App\Enums;

enum SaleType: string
{
    case Vente = 'vente';
    case Echange = 'echange';

    public function label(): string
    {
        return match ($this) {
            self::Vente => 'Vente',
            self::Echange => 'Échange',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Vente => 'bg-primary',
            self::Echange => 'bg-warning text-dark',
        };
    }
}
