<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Repository;

interface ScopeProviderInterface
{
    /**
     * @return string[]
     */
    public function getScopes(): array;

    public function hasScope(string $scope): bool;
}
