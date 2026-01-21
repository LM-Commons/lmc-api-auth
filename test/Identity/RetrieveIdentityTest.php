<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Identity;

use Lmc\Api\Auth\Exception\AuthenticatedIdentityNotFoundException;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Lmc\Api\Auth\Identity\RetrieveIdentity;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

final class RetrieveIdentityTest extends TestCase
{
    public function testRetrieveIdentity(): void
    {
        $identity = new AuthenticatedIdentity('foo');
        $request  = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn($identity);
        $this->assertSame($identity, RetrieveIdentity::fromRequest($request));
    }

    public function testRetrieveIdentityException(): void
    {
        $foo     = new stdClass();
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(IdentityInterface::class)
            ->willReturn($foo);
        $this->expectException(AuthenticatedIdentityNotFoundException::class);
        RetrieveIdentity::fromRequest($request);
    }
}
