<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

interface IdentityInterface
{
    public function getAuthenticationIdentity(): mixed;
}
