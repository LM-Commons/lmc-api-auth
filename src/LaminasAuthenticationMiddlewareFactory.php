<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

final class LaminasAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): LaminasAuthenticationMiddleware
    {
        /** @psalm-suppress MixedArgument */
        return new LaminasAuthenticationMiddleware(
            $container->get(AuthenticationService::class),
        );
    }
}
