<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Repository\ApiAccessKeyRepositoryInterface;
use Psr\Container\ContainerInterface;

final class ApiAccessKeyAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ApiAccessKeyAuthenticationMiddleware
    {
        /** @psalm-suppress MixedArgument */
        return new ApiAccessKeyAuthenticationMiddleware(
            $container->get(ApiAccessKeyRepositoryInterface::class),
        );
    }
}
