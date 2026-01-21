<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\ApiAccessKeyAuthenticationMiddleware;
use Lmc\Api\Auth\ApiAccessKeyAuthenticationMiddlewareFactory;
use Lmc\Api\Auth\Repository\ApiAccessKeyRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ApiAccessKeyAuthenticationMiddlewareFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')
            ->with(ApiAccessKeyRepositoryInterface::class)
            ->willReturn($this->createMock(ApiAccessKeyRepositoryInterface::class));
        $factory = new ApiAccessKeyAuthenticationMiddlewareFactory();
        $this->assertInstanceOf(ApiAccessKeyAuthenticationMiddleware::class, $factory($container));
    }
}
