<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationRestMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationRestMiddleware
    {
        /** @var array $config */
        $config = $container->get('config');
        /** @var array $config */
        $config = $config['lmc_api'] ?? [];
        /** @var array $config */
        $config = $config['rest'] ?? [];
        return new AuthorizationRestMiddleware(
            $container->get(AuthorizationInterface::class),
            $container->get(ResponseFactoryInterface::class),
            $config,
        );
    }
}
