<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth;

use Lmc\Api\Auth\Adapter\ApiAccessKeyInterface;
use Lmc\Api\Auth\ApiAccessKeyAuthenticationMiddleware;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Lmc\Api\Auth\Repository\ApiAccessKeyRepositoryInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ApiAccessKeyAuthenticationMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $request;
    private RequestHandlerInterface&MockObject $handler;

    #[Override]
    protected function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        parent::setUp();
    }

    public function testWithIdentity(): void
    {
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(new GuestIdentity());
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $middleware = new ApiAccessKeyAuthenticationMiddleware(
            $this->createMock(ApiAccessKeyRepositoryInterface::class),
        );
        $middleware->process($this->request, $this->handler);
    }

    public function testWithNullClientIdAndSecret(): void
    {
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(null);
        $this->request->expects($this->exactly(2))->method('getQueryParams')
            ->willReturn([
                'client_id'     => null,
                'client_secret' => null,
            ]);
        $this->request->expects($this->once())->method('withAttribute')
            ->with(IdentityInterface::class, new GuestIdentity())
            ->willReturnSelf();
        $this->handler->expects($this->once())->method('handle');
        $middleware = new ApiAccessKeyAuthenticationMiddleware(
            $this->createMock(ApiAccessKeyRepositoryInterface::class),
        );
        $middleware->process($this->request, $this->handler);
    }

    public function testWithInvalidClientId(): void
    {
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(null);
        $this->request->expects($this->exactly(2))->method('getQueryParams')
            ->willReturn([
                'client_id'     => 'foo',
                'client_secret' => 'bar',
            ]);
        $apiRepository = $this->createMock(ApiAccessKeyRepositoryInterface::class);
        $apiRepository->expects($this->once())->method('getByClientId')
            ->with('foo')->willReturn(null);
        $this->request->expects($this->once())->method('withAttribute')
            ->with(IdentityInterface::class, new GuestIdentity())
            ->willReturnSelf();
        $this->handler->expects($this->once())->method('handle');
        $middleware = new ApiAccessKeyAuthenticationMiddleware($apiRepository);
        $middleware->process($this->request, $this->handler);
    }

    public function testWithNotMatchingSecret(): void
    {
        $apiAccessKey = $this->createMock(ApiAccessKeyInterface::class);
        $apiAccessKey->expects($this->once())->method('getClientSecret')
            ->willReturn('foo');
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(null);
        $this->request->expects($this->exactly(2))->method('getQueryParams')
            ->willReturn([
                'client_id'     => 'foo',
                'client_secret' => 'bar',
            ]);
        $apiRepository = $this->createMock(ApiAccessKeyRepositoryInterface::class);
        $apiRepository->expects($this->once())->method('getByClientId')
            ->with('foo')->willReturn($apiAccessKey);
        $this->request->expects($this->once())->method('withAttribute')
            ->with(IdentityInterface::class, new GuestIdentity())
            ->willReturnSelf();
        $this->handler->expects($this->once())->method('handle');
        $middleware = new ApiAccessKeyAuthenticationMiddleware($apiRepository);
        $middleware->process($this->request, $this->handler);
    }

    public function testWithValidIdentity(): void
    {
        $apiAccessKey = $this->createMock(ApiAccessKeyInterface::class);
        $apiAccessKey->expects($this->once())->method('getClientSecret')
            ->willReturn('bar');
        $this->request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn(null);
        $this->request->expects($this->exactly(2))->method('getQueryParams')
            ->willReturn([
                'client_id'     => 'foo',
                'client_secret' => 'bar',
            ]);
        $apiRepository = $this->createMock(ApiAccessKeyRepositoryInterface::class);
        $apiRepository->expects($this->once())->method('getByClientId')
            ->with('foo')->willReturn($apiAccessKey);
        $this->request->expects($this->once())->method('withAttribute')
            ->with(
                IdentityInterface::class,
                $this->isInstanceOf(AuthenticatedIdentity::class),
            )
            ->willReturnSelf();
        $this->handler->expects($this->once())->method('handle');
        $middleware = new ApiAccessKeyAuthenticationMiddleware($apiRepository);
        $middleware->process($this->request, $this->handler);
    }
}
