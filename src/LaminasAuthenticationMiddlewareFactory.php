<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

class LaminasAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): LaminasAuthenticationMiddleware
    {
        return new LaminasAuthenticationMiddleware(
            $container->get(AuthenticationService::class),
        );
    }
}
