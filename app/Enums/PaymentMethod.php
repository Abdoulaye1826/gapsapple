<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Wave = 'wave';
    case OrangeMoney = 'orange_money';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Wave => 'Wave',
            self::OrangeMoney => 'Orange Money',
            self::Cash => 'Espèces',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Wave => 'bi-phone',
            self::OrangeMoney => 'bi-phone-fill',
            self::Cash => 'bi-cash-coin',
        };
    }
}
