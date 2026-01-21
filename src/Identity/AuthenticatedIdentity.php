<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Override;

final class AuthenticatedIdentity implements IdentityInterface
{
    protected mixed $identity;

    public function __construct(mixed $identity)
    {
        $this->identity = $identity;
    }

    #[Override]
    public function getAuthenticationIdentity(): mixed
    {
        return $this->identity;
    }
}
