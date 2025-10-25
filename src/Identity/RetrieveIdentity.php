<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Lmc\Api\Auth\Exception\AuthenticatedIdentityNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

final class RetrieveIdentity
{
    public static function fromRequest(ServerRequestInterface $req): AuthenticatedIdentity
    {
        $identity = $req->getAttribute(AuthenticatedIdentity::class);
        if (! $identity instanceof AuthenticatedIdentity) {
            throw AuthenticatedIdentityNotFoundException::forMissingIdentityAttribute();
        }
        return $identity;
    }
}
