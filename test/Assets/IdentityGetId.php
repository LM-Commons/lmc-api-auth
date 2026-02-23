<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Assets;

use Lmc\Api\Auth\Identity\IdentityInterface;
use Override;

final class IdentityGetId implements IdentityInterface
{
    public function getId(): string
    {
        return 'foo';
    }

    #[Override]
    public function getAuthenticationIdentity(): mixed
    {
        return null;
    }

    #[Override]
    public function getRoleId(): string
    {
        return $this->getId();
    }
}
