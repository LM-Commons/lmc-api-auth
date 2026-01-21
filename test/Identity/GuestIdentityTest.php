<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Identity;

use Lmc\Api\Auth\Identity\GuestIdentity;
use PHPUnit\Framework\TestCase;

final class GuestIdentityTest extends TestCase
{
    public function testConstruct(): void
    {
        $guest = new GuestIdentity();
        $this->assertEquals('guest', $guest->getRoleId());
        $this->assertNull($guest->getAuthenticationIdentity());
    }
}
