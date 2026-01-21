<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

class AuthenticatedIdentity implements IdentityInterface
{
    protected mixed $identity;

    public function __construct(mixed $identity)
    {
        $this->identity = $identity;
    }

    public function getAuthenticationIdentity(): mixed
    {
        return $this->identity;
    }
}
