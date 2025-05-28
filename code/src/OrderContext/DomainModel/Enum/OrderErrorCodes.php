<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Enum;

enum OrderErrorCodes: int
{
    case SAVE_ORDER_ERROR = 100;
}