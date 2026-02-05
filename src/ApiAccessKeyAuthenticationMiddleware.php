<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Adapter\ApiAccessKeyInterface;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Lmc\Api\Auth\Repository\ApiAccessKeyRepositoryInterface;
use Override;
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
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // if there is already an identity, do nothing
        $identity = $request->getAttribute(IdentityInterface::class);
        if ($identity !== null && ! $identity instanceof GuestIdentity) {
            return $handler->handle($request);
        }

        /** @var ?string $clientId */
        $clientId = $request->getQueryParams()['client_id'] ?? null;
        /** @var ?string $clientSecret */
        $clientSecret = $request->getQueryParams()['client_secret'] ?? null;
        if ($clientId === null && $clientSecret === null) {
            return $handler->handle($request->withAttribute(
                IdentityInterface::class,
                new GuestIdentity()
            ));
        }

        /** @psalm-suppress PossiblyNullArgument */
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
