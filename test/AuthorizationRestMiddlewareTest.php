<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Authorization\AclAuthorizationFactory;
use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\AuthorizationRestMiddleware;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use LmcTest\Api\Auth\Assets\IdentityGetId;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(AuthorizationRestMiddleware::class)]
final class AuthorizationRestMiddlewareTest extends TestCase
{
    private ServerRequestInterface&Stub $request;
    private RequestHandlerInterface&MockObject $handler;
    private AuthorizationInterface&MockObject $authorization;
    private ResponseFactoryInterface&MockObject $responseFactory;

    #[Override]
    public function setUp(): void
    {
        $this->request         = $this->createStub(ServerRequestInterface::class);
        $this->handler         = $this->createMock(RequestHandlerInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->authorization   = $this->createMock(AuthorizationInterface::class);
        parent::setUp();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoIdentity(): void
    {
        $this->request->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, null],
            ]);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoRoute(): void
    {
        $this->request->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, new IdentityGetId()],
                [RouteResult::class, null],
            ]);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoRouteMatchName(): void
    {
        $routeResult = RouteResult::fromRouteFailure([]);
        $this->request->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, new IdentityGetId()],
                [RouteResult::class, $routeResult],
            ]);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
        $middleware->process($this->request, $this->handler);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @param array<array-key, array<array-key, mixed>> $config
     */
    #[DataProvider('configProvider')]
    #[AllowMockObjectsWithoutExpectations]
    public function testRouteMatchName(
        array $config,
        IdentityInterface $identity,
        string $method,
        bool $isAuthorized,
        ?int $id = null,
    ): void {
        $route       = new Route(
            'foo',
            $this->createStub(MiddlewareInterface::class),
            Route::HTTP_METHOD_ANY,
            'foo'
        );
        $routeResult = RouteResult::fromRoute($route);
        $this->request->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, $identity],
                [RouteResult::class, $routeResult],
                ['id', $id],
            ]);
        $this->request->method('getMethod')->willReturn($method);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(403);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            ['config', $config],
        ]);
        $aclAuthorization = (new AclAuthorizationFactory())($container);

        if ($isAuthorized) {
            $this->handler->expects($this->once())->method('handle')->with($this->request);
        } else {
            $this->responseFactory->method('createResponse')->with(403, 'Forbidden')->willReturn($response);
        }

        $middleware = new AuthorizationRestMiddleware(
            $aclAuthorization,
            $this->responseFactory,
            $config['lmc_api']['rest']
        );
        $response   = $middleware->process($this->request, $this->handler);
        if (! $isAuthorized) {
            $this->assertSame(403, $response->getStatusCode());
        }
    }

    public static function configProvider(): array
    {
        return [
            'empty_config'                                 => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'foo' => [
                                    'actions' => [
                                        'GET' => true,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [],
                    ],
                ],
                'identity'     => new IdentityGetId(),
                'method'       => 'GET',
                'isAuthorized' => true,
            ],
            'collection_with_identity'                     => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'foo' => [
                                    'collection' => [
                                        'GET' => true,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [],
                        ],
                    ],
                ],
                'identity'     => new IdentityGetId(),
                'method'       => 'GET',
                'isAuthorized' => true,
            ],
            'collection_no_identity_deny_on_get'           => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'foo'             => [
                                    'collection' => [
                                        'GET' => true,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => false,
            ],
            'collection_no_identity_allow_on_get'          => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'foo'             => [
                                    'collection' => [
                                        'GET' => false,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => true,
            ],
            'get_entity_no_identity_deny_on_get'           => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'foo'             => [
                                    'entity' => [
                                        'GET' => true,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [
                                'route_identifier_name' => 'id',
                            ],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => false,
                'id'           => 1,
            ],
            'get_entity_no_identity_allow_on_get'          => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'foo'             => [
                                    'entity' => [
                                        'GET' => false,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [
                                'route_identifier_name' => 'id',
                            ],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => true,
                'id'           => 1,
            ],
            'get_entity_no_entity_identifier_allow_on_get' => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => true,
                                'foo'             => [
                                    'entity' => [
                                        'GET' => false,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [
                                'route_identifier_name' => 'id',
                            ],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => false,
                'id'           => null,
            ],
            'get_entity_no_entity_identifier_deny_on_get'  => [
                'config'       => [
                    'lmc_api' => [
                        'authentication' => [
                            'authorization' => [
                                'deny_by_default' => false,
                                'foo'             => [
                                    'collection' => [
                                        'GET' => true,
                                    ],
                                    'entity'     => [
                                        'GET' => false,
                                    ],
                                ],
                            ],
                        ],
                        'rest'           => [
                            'foo' => [
                                'route_identifier_name' => 'id',
                            ],
                        ],
                    ],
                ],
                'identity'     => new GuestIdentity(),
                'method'       => 'GET',
                'isAuthorized' => false,
                'id'           => null,
            ],
        ];
    }
}
