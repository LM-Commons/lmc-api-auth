<?php

namespace Lmc\Api\Auth\Authorization;

use Lmc\Api\Auth\Identity\IdentityInterface;

interface AuthorizationInterface
{
    public function isAuthorized(IdentityInterface $identity, mixed $resource, mixed $privilege): bool;
}
