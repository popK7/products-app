<?php

declare(strict_types=1);

namespace App\Enum;

enum InventoryStatus: string
{
    case INSTOCK = 'instock';
    case LOWSTOCK = 'lowstock';
    case OUTOFSTOCK = 'outstock';
}
