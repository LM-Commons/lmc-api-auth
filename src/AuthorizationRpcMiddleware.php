<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AuthorizationInterface;
use Lmc\Api\Auth\Identity\IdentityInterface;
use Mezzio\Router\RouteResult;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

final readonly class AuthorizationRpcMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationInterface   $authorization,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $request->getAttribute(IdentityInterface::class);
        if (! $identity instanceof IdentityInterface) {
            return $handler->handle($request);
        }

        /** @var ?RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        if (null === $routeResult) {
            return $handler->handle($request);
        }
        $routeMatchName = $routeResult->getMatchedRouteName();

        if (false === $routeMatchName) {
            return $handler->handle($request);
        }

        $resource = sprintf('%s', $routeMatchName);

        if ($this->authorization->isAuthorized($identity, $resource, $request->getMethod())) {
            return $handler->handle($request);
        }

        return $this->responseFactory->createResponse(403, 'Forbidden');
    }
}
