<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

class GuestIdentity implements IdentityInterface
{
    protected static string $identity = 'guest';

    public function __construct()
    {
    }

    public function getRoleId(): string
    {
        return static::$identity;
    }

    public function getAuthenticationIdentity(): mixed
    {
        return null;
    }
}
