<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\AuthorizationRpcMiddleware;
use Lmc\Api\Auth\AuthorizationRpcMiddlewareFactory;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

#[CoversClass(AuthorizationRpcMiddlewareFactory::class)]
final class AuthorizationRpcMiddlewareFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [AuthorizationInterface::class, $this->createMock(AuthorizationInterface::class)],
                [ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class)],
            ]);
        $factory = new AuthorizationRpcMiddlewareFactory();
        $this->assertInstanceOf(AuthorizationRpcMiddleware::class, $factory($container));
    }
}
