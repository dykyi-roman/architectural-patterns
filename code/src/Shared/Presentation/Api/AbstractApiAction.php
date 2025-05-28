<?php

declare(strict_types=1);

namespace Shared\Presentation\Api;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', description: 'REST API', title: 'REST API')]
abstract class AbstractApiAction
{
}
