<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Enofily\AuthDb\Entity\ApiAccessKey;
use Enofily\AuthDb\Service\ApiAccessKeyService;
use Lmc\Api\Auth\Adapter\ApiAccessKeyInterface;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Lmc\Api\Auth\Repository\ApiAccessKeyRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class ApiAccessKeyAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ApiAccessKeyRepositoryInterface $apiAccessKeyRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // if there is already an identity, do nothing
        if ($request->getAttribute(IdentityInterface::class) !== null) {
            return $handler->handle($request);
        }

        $clientId     = $request->getQueryParams()['client_id'] ?? null;
        $clientSecret = $request->getQueryParams()['client_secret'] ?? null;
        if ($clientId === null && $clientSecret === null) {
            return $handler->handle($request
            ->withAttribute(IdentityInterface::class, new GuestIdentity()));
        }

        $apiAccessKey = $this->apiAccessKeyRepository->getByClientId($clientId);
        if (! $apiAccessKey instanceof ApiAccessKeyInterface) {
            return $handler->handle($request
            ->withAttribute(IdentityInterface::class, new GuestIdentity()));
        }

        if ($clientSecret !== $apiAccessKey->getClientSecret()) {
            return $handler->handle($request
            ->withAttribute(IdentityInterface::class, new GuestIdentity()));
        }

        return $handler->handle($request
        ->withAttribute(IdentityInterface::class, new AuthenticatedIdentity($apiAccessKey)));
    }
}
