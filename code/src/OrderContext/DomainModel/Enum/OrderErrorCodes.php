<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Enum;

enum OrderErrorCodes: int
{
    case SAVE_ORDER_ERROR = 100;
    case HISTORY_NOT_FOUND = 101;
}
