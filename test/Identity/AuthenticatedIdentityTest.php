<?php

declare(strict_types=1);

namespace LmcTest\Api\Auth\Identity;

use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AuthenticatedIdentityTest extends TestCase
{
    public function testConstruct(): void
    {
        $a = new AuthenticatedIdentity(new stdClass());
        $this->assertInstanceOf(stdClass::class, $a->getAuthenticationIdentity());
    }
}
