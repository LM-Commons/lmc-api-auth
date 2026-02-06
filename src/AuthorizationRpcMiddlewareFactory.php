<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationRpcMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationRpcMiddleware
    {
        return new AuthorizationRpcMiddleware(
            $container->get(AuthorizationInterface::class),
            $container->get(ResponseFactoryInterface::class),
        );
    }
}
