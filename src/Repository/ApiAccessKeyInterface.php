<?php

namespace Lmc\Api\Auth\Repository;

interface ApiAccessKeyInterface extends ScopeProviderInterface
{
    public function getClientId(): string;

    public function getClientSecret(): string;
}
