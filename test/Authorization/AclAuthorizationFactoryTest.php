<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Authorization;

use Lmc\Api\Auth\Authorization\AclAuthorizationFactory;
use Lmc\Api\Auth\Identity\GuestIdentity;
use LmcTest\Api\Auth\Assets\IdentityGetId;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AclAuthorizationFactoryTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    #[Override]
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        parent::setUp();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEmptyConfig(): void
    {
        $config = [
            'lmc_api' => [
                'authentication' => [
                    'authorization' => [],
                ],
            ],
        ];
        $this->container->expects($this->once())->method('get')
            ->with('config')
            ->willReturn($config);
        $factory          = new AclAuthorizationFactory();
        $aclAuthorization = $factory($this->container);
        $this->assertFalse($aclAuthorization->hasResource('foo'));
        $this->assertFalse($aclAuthorization->hasRole('foo'));
        $this->assertTrue($aclAuthorization->isAuthorized(new IdentityGetId(), 'bar', 'foo'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DataProvider('configProvider')]
    public function testConfig(array $config, string $resource, string $privilege, bool $isAuthorized): void
    {
        $this->container->expects($this->once())->method('get')
            ->with('config')
            ->willReturn($config);
        $factory          = new AclAuthorizationFactory();
        $aclAuthorization = $factory($this->container);
        $this->assertEquals($isAuthorized, $aclAuthorization->isAuthorized(new GuestIdentity(), $resource, $privilege));
    }

    /** @psalm-return  iterable<array-key, array<array-key, mixed>> */
    public static function configProvider(): array
    {
        return [
            'empty config'              => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [],
                        ],
                    ],
                ],
                'resource'     => 'bar',
                'privilege'    => 'for',
                'isAuthorized' => true,
            ],
            'deny_by_default true'      => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                            ],
                        ],
                    ],
                ],
                'resource'     => 'bar',
                'privilege'    => 'for',
                'isAuthorized' => false,
            ],
            'actions denied'            => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'route1'          => [
                                    'actions' => [
                                        'GET' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1',
                'privilege'    => 'GET',
                'isAuthorized' => false,
            ],
            'actions allowed'           => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'route1'          => [
                                    'actions' => [
                                        'POST' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1',
                'privilege'    => 'POST',
                'isAuthorized' => true,
            ],
            'actions denied by default' => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'route1'          => [
                                    'actions' => [
                                        'POST' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1',
                'privilege'    => 'GET',
                'isAuthorized' => false,
            ],
            'collection allowed'        => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'route1'          => [
                                    'collection' => [
                                        'POST' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1::collection',
                'privilege'    => 'POST',
                'isAuthorized' => false,
            ],
            'entity allowed'            => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'route1'          => [
                                    'entity' => [
                                        'POST' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1::entity',
                'privilege'    => 'POST',
                'isAuthorized' => false,
            ],
            'entity default denied'     => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'route1'          => [
                                    'entity' => [
                                        'default' => true,
                                        'POST'    => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1::entity',
                'privilege'    => 'POST',
                'isAuthorized' => false,
            ],
            'entity default allowed'    => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'route1'          => [
                                    'entity' => [
                                        'default' => true,
                                        'POST'    => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1::entity',
                'privilege'    => 'POST',
                'isAuthorized' => true,
            ],
            'entity empty rules'        => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'route1'          => [
                                    'actions' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                'resource'     => 'route1::entity',
                'privilege'    => 'POST',
                'isAuthorized' => true,
            ],
        ];
    }
}
