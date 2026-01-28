<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Laminas\Authentication\AuthenticationService;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Lmc\Api\Auth\LaminasAuthenticationMiddleware;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LaminasAuthenticationMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $request;
    private RequestHandlerInterface&MockObject $handler;
    private AuthenticationService&MockObject $authenticationService;

    #[Override]
    protected function setUp(): void
    {
        $this->request               = $this->createMock(ServerRequestInterface::class);
        $this->handler               = $this->createMock(RequestHandlerInterface::class);
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        parent::setUp();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testWithoutIdentity(): void
    {
        $this->authenticationService->expects($this->once())->method('hasIdentity')->willReturn(false);
        $this->handler->expects($this->once())->method('handle')
            ->with($this->isInstanceOf(ServerRequestInterface::class));
        $this->request->expects($this->once())->method('withAttribute')
            ->with(IdentityInterface::class, $this->isInstanceOf(GuestIdentity::class))
            ->willReturnSelf();
        $middleware = new LaminasAuthenticationMiddleware($this->authenticationService);
        $middleware->process($this->request, $this->handler);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testWithIdentity(): void
    {
        $identity = 'foo';
        $this->authenticationService->expects($this->once())->method('hasIdentity')->willReturn(true);
        $this->authenticationService->expects($this->once())->method('getIdentity')->willReturn($identity);
        $this->handler->expects($this->once())->method('handle')
            ->with($this->isInstanceOf(ServerRequestInterface::class));
        $this->request->expects($this->once())->method('withAttribute')
            ->with(IdentityInterface::class, $this->isInstanceOf(AuthenticatedIdentity::class))
            ->willReturnSelf();
        $middleware = new LaminasAuthenticationMiddleware($this->authenticationService);
        $middleware->process($this->request, $this->handler);
    }
}
