<?php

namespace Lmc\Api\Auth\Identity;

interface IdentityInterface
{
    public function getAuthenticationIdentity(): mixed;
}
