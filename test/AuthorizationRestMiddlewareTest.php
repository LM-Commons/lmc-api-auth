<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\AuthorizationRestMiddleware;
use Lmc\Api\Auth\Identity\IdentityInterface;
use LmcTest\Api\Auth\Assets\IdentityGetId;
use Mezzio\Router\RouteResult;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(AuthorizationRestMiddleware::class)]
final class AuthorizationRestMiddlewareTest extends TestCase
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
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
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
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
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
        $middleware = new AuthorizationRestMiddleware($this->authorization, $this->responseFactory, []);
        $middleware->process($this->request, $this->handler);
    }
}
