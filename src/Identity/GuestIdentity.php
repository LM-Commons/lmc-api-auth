<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Override;

final class GuestIdentity implements IdentityInterface
{
    protected static string $identity = 'guest';

    public function __construct()
    {
    }

    public function getRoleId(): string
    {
        return static::$identity;
    }

    #[Override]
    public function getAuthenticationIdentity(): mixed
    {
        return null;
    }
}
