<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Identity;

use Lmc\Api\Auth\Exception\AuthenticatedIdentityNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

final class RetrieveIdentity
{
    public static function fromRequest(ServerRequestInterface $request): IdentityInterface
    {
        $identity = $request->getAttribute(IdentityInterface::class);
        if (! $identity instanceof IdentityInterface) {
            throw AuthenticatedIdentityNotFoundException::forMissingIdentityAttribute();
        }
        return $identity;
    }
}
