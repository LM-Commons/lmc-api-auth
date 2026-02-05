<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Laminas\Authentication\AuthenticationService;
use Lmc\Api\Auth\Identity\AuthenticatedIdentity;
use Lmc\Api\Auth\Identity\GuestIdentity;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class LaminasAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationService $authenticationService,
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

        if ($this->authenticationService->hasIdentity()) {
            /** @var mixed $identity */
            $identity     = $this->authenticationService->getIdentity();
            $authIdentity = new AuthenticatedIdentity($identity);
        } else {
            $authIdentity = new GuestIdentity();
        }
        return $handler->handle($request->withAttribute(IdentityInterface::class, $authIdentity));
    }
}
