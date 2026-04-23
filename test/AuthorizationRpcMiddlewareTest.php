<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\AuthorizationRpcMiddleware;
use Lmc\Api\Auth\Identity\IdentityInterface;
use LmcTest\Api\Auth\Assets\IdentityGetId;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(AuthorizationRpcMiddleware::class)]
final class AuthorizationRpcMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $request;
    private RequestHandlerInterface&MockObject $handler;
    private AuthorizationInterface&MockObject $authorization;
    private ResponseFactoryInterface&MockObject $responseFactory;

    #[Override]
    public function setUp(): void
    {
        $this->request         = $this->createMock(ServerRequestInterface::class);
        $this->handler         = $this->createMock(RequestHandlerInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->authorization   = $this->createMock(AuthorizationInterface::class);
        parent::setUp();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoIdentity(): void
    {
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(null);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRpcMiddleware($this->authorization, $this->responseFactory);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoRoute(): void
    {
        $this->request->expects($this->exactly(2))->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, new IdentityGetId()],
                [RouteResult::class, null],
            ]);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRpcMiddleware($this->authorization, $this->responseFactory);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNoRouteMatchName(): void
    {
        $routeResult = RouteResult::fromRouteFailure([]);
        $this->request->expects($this->exactly(2))->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, new IdentityGetId()],
                [RouteResult::class, $routeResult],
            ]);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRpcMiddleware($this->authorization, $this->responseFactory);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAuthorized(): void
    {
        $route       = new Route(
            '/foo',
            $this->createStub(MiddlewareInterface::class),
            null,
            'foo'
        );
        $routeResult = RouteResult::fromRoute($route);
        $identity    = new IdentityGetId();
        $this->request->expects($this->exactly(2))->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, $identity],
                [RouteResult::class, $routeResult],
            ]);
        $this->request->expects($this->once())->method('getMethod')->willReturn('GET');
        $this->authorization->expects($this->once())->method('isAuthorized')
            ->with($identity, 'foo', 'GET')
            ->willReturn(true);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new AuthorizationRpcMiddleware($this->authorization, $this->responseFactory);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testNotAuthorized(): void
    {
        $route       = new Route(
            '/foo',
            $this->createStub(MiddlewareInterface::class),
            null,
            'foo'
        );
        $routeResult = RouteResult::fromRoute($route);
        $identity    = new IdentityGetId();
        $this->request->expects($this->exactly(2))->method('getAttribute')
            ->willReturnMap([
                [IdentityInterface::class, $identity],
                [RouteResult::class, $routeResult],
            ]);
        $this->request->expects($this->once())->method('getMethod')->willReturn('GET');
        $this->authorization->expects($this->once())->method('isAuthorized')
            ->with($identity, 'foo', 'GET')
            ->willReturn(false);
        $this->responseFactory->expects($this->once())->method('createResponse')
            ->with(403, 'Forbidden');
        $middleware = new AuthorizationRpcMiddleware($this->authorization, $this->responseFactory);
        $middleware->process($this->request, $this->handler);
    }
}
