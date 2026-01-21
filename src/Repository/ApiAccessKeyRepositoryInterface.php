<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Repository;

use Lmc\Api\Auth\Adapter\ApiAccessKeyInterface;

interface ApiAccessKeyRepositoryInterface
{
    public function getByClientId(string $clientId): ?ApiAccessKeyInterface;
}
