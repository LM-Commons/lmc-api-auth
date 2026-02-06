<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Laminas\Permissions\Acl\Role\RoleInterface;

interface IdentityInterface extends RoleInterface
{
    public function getAuthenticationIdentity(): mixed;
}
