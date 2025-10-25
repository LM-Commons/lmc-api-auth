<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'lmc_api'      => $this->getConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories' => [
                ApiAccessKeyAuthenticationMiddleware::class => ApiAccessKeyAuthenticationMiddlewareFactory::class,
                LaminasAuthenticationMiddleware::class      => LaminasAuthenticationMiddlewareFactory::class,
            ],
        ];
    }

    private function getConfig(): array
    {
        return [];
    }
}
