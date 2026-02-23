<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\AuthorizationRestMiddleware;
use Lmc\Api\Auth\AuthorizationRestMiddlewareFactory;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

#[CoversClass(AuthorizationRestMiddlewareFactory::class)]
final class AuthorizationRestMiddlewareFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))->method('get')
            ->willReturnMap([
                [AuthorizationInterface::class, $this->createMock(AuthorizationInterface::class)],
                [ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class)],
                [
                    'config',
                    [
                        'lmc_api' => [
                            'rest' => [],
                        ],
                    ],
                ],
            ]);
        $factory = new AuthorizationRestMiddlewareFactory();
        $this->assertInstanceOf(AuthorizationRestMiddleware::class, $factory($container));
    }
}
