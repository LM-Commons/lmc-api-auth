<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Repository;

use Lmc\Api\Auth\Identity\IdentityInterface;

interface UserRepositoryInterface
{
    public function getByUserId(int|string $userId): ?IdentityInterface;
}
