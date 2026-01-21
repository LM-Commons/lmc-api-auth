<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Laminas\Authentication\AuthenticationService;
use Lmc\Api\Auth\LaminasAuthenticationMiddleware;
use Lmc\Api\Auth\LaminasAuthenticationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class LaminasAuthenticationMiddlewareFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')
            ->with(AuthenticationService::class)
            ->willReturn($this->createMock(AuthenticationService::class));
        $factory = new LaminasAuthenticationMiddlewareFactory();
        $this->assertInstanceOf(LaminasAuthenticationMiddleware::class, $factory($container));
    }
}
