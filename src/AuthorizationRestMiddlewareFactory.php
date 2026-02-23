<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationRestMiddlewareFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AuthorizationRestMiddleware
    {
        /** @var array $config */
        $config = $container->get('config');
        /** @var array $config */
        $config = $config['lmc_api'] ?? [];
        /** @var array $config */
        $config = $config['rest'] ?? [];
        /** @psalm-suppress MixedArgument */
        return new AuthorizationRestMiddleware(
            $container->get(AuthorizationInterface::class),
            $container->get(ResponseFactoryInterface::class),
            $config,
        );
    }
}
