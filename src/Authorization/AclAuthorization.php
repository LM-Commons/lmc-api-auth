<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Authorization;

use Laminas\Permissions\Acl\Acl;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Override;

final class AclAuthorization extends Acl implements AuthorizationInterface
{
    #[Override]
    public function isAuthorized(IdentityInterface $identity, mixed $resource, mixed $privilege): bool
    {
        if (null !== $resource && (! $this->hasResource($resource))) {
            $this->addResource($resource);
        }

        if (! $this->hasRole($identity)) {
            $this->addRole($identity);
        }

        return $this->isAllowed($identity, $resource, $privilege);
    }
}
