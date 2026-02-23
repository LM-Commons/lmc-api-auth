<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Repository;

interface UserRepositoryInterface
{
    public function getByUserId(int|string $userId): mixed;
}
