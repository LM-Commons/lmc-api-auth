<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Identity;

use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use LmcTest\Api\Auth\Assets\IdentityGetId;
use LmcTest\Api\Auth\Assets\IdentityNoGetId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticatedIdentity::class)]
final class AuthenticatedIdentityTest extends TestCase
{
    public function testConstruct(): void
    {
        $a = new AuthenticatedIdentity(new IdentityNoGetId());
        $this->assertInstanceOf(IdentityNoGetId::class, $a->getAuthenticationIdentity());
    }

    public function testConstructGetId(): void
    {
        $a = new AuthenticatedIdentity(new IdentityGetId());
        $this->assertInstanceOf(IdentityGetId::class, $a->getAuthenticationIdentity());
        $this->assertEquals('foo', $a->getRoleId());
    }
}
