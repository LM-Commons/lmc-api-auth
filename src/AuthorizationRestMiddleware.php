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

final readonly class AuthorizationRestMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationInterface   $authorization,
        private ResponseFactoryInterface $responseFactory,
        private array $restConfig,
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

        $resource = $this->buildResource($routeMatchName, $request);

        if ($this->authorization->isAuthorized($identity, $resource, $request->getMethod())) {
            return $handler->handle($request);
        }

        return $this->responseFactory->createResponse(403, 'Forbidden');
    }

    private function buildResource(string $routeMatchName, ServerRequestInterface $request): string
    {
        /** @var array|null $restConfig */
        $restConfig = $this->restConfig[$routeMatchName] ?? null;
        if (null === $restConfig) {
            // Return a resource name as if it was a RPC call
            return sprintf('%s', $routeMatchName);
        }
        $identifier = $restConfig['route_identifier_name'] ?? null;
        if (null === $identifier) {
            // assume collection resource
            return sprintf('%s::collection', $routeMatchName);
        }
        if ($request->getAttribute($identifier) !== null) {
            return sprintf('%s::entity', $routeMatchName);
        }
        return sprintf('%s::collection', $routeMatchName);
    }
}
