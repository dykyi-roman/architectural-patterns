<?php

declare(strict_types=1);

namespace Shared\DomainModel\Enum;

enum GeneralErrorCode: int
{
    case UNEXPECTED_ERROR = 1;
    case UNKNOWN_ERROR = 2;
}