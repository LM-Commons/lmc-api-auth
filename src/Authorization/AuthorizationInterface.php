<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Authorization;

use Lmc\Api\Auth\Identity\IdentityInterface;

interface AuthorizationInterface
{
    public function isAuthorized(IdentityInterface $identity, string $resource, string $privilege): bool;
}
