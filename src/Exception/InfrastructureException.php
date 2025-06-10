<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class InfrastructureException extends RuntimeException implements InfrastructureExceptionInterface
{
}
