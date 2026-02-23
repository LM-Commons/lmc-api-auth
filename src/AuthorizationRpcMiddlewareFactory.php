<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationRpcMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AuthorizationRpcMiddleware
    {
        /** @psalm-suppress MixedArgument */
        return new AuthorizationRpcMiddleware(
            $container->get(AuthorizationInterface::class),
            $container->get(ResponseFactoryInterface::class),
        );
    }
}
