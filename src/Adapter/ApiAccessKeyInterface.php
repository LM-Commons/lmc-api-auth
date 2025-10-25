<?php

namespace Lmc\Api\Auth\Adapter;

interface ApiAccessKeyInterface
{
    public function getClientId(): string;

    public function getClientSecret(): string;

    public function hasScope(string $scope): bool;

    /**
     * @return string[]
     */
    public function getScopes(): array;
}
