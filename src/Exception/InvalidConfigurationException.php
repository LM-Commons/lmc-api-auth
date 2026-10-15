<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Exception;

use RuntimeException;

final class InvalidConfigurationException extends RuntimeException implements ExceptionInterface
{
}
