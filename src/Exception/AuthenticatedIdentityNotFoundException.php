<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Exception;

use RuntimeException;

final class AuthenticatedIdentityNotFoundException extends RuntimeException implements ExceptionInterface
{
    public static function forMissingIdentityAttribute(): self
    {
        return new self(
            'An identity was not found. Ensure that you pipe an API authentication middleware prior to'
            . ' attempting to retrieve an identity instance.'
        );
    }

}
