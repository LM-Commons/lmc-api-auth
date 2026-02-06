<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Override;

use function method_exists;

final class AuthenticatedIdentity implements IdentityInterface
{
    protected mixed $identity;
    protected string $name = '';

    public function __construct(mixed $identity)
    {
        $this->identity = $identity;
        if (method_exists($identity, 'getId')) {
            $this->name = (string) $this->identity->getId();
        }
    }

    #[Override]
    public function getAuthenticationIdentity(): mixed
    {
        return $this->identity;
    }

    #[Override]
    public function getRoleId(): string
    {
        return $this->name;
    }
}
