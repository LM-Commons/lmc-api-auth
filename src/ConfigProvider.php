<?php

declare(strict_types=1);

namespace Lmc\Api\Auth;

use Lmc\Api\Auth\Authorization\AclAuthorization;
use Lmc\Api\Auth\Authorization\AclAuthorizationFactory;
use Lmc\Api\Auth\Authorization\AuthorizationInterface;

final class ConfigProvider
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
            'aliases' => [
                AuthorizationInterface::class => AclAuthorization::class,
            ],
            'factories' => [
                AclAuthorization::class                     => AclAuthorizationFactory::class,
                ApiAccessKeyAuthenticationMiddleware::class => ApiAccessKeyAuthenticationMiddlewareFactory::class,
                LaminasAuthenticationMiddleware::class      => LaminasAuthenticationMiddlewareFactory::class,
                AuthorizationRpcMiddleware::class           => AuthorizationRpcMiddlewareFactory::class,
            ],
        ];
    }

    private function getConfig(): array
    {
        return [
            'authentication' => [
                'authorization' => [
                    // Toggle the following to true to change the ACL creation to
                    // require an authenticated user by default, and thus selectively
                    // allow unauthenticated users based on the rules.
                    'deny_by_default' => false,

                    /*
                     * Rules indicating what routes are behind authentication.
                     *
                     * Keys are route names.
                     *
                     * Values are arrays with either the key "actions" and/or one or
                     * more of the keys "collection" and "entity".
                     *
                     * The "actions" key will be a set of action name/method pairs.
                     * The "collection" and "entity" keys will have method values.
                     *
                     * Method values are arrays of HTTP method/boolean pairs. By
                     * default, if an HTTP method is not present in the list, it is
                     * assumed to be open (i.e., not require authentication). The
                     * special key "default" can be used to set the default flag for
                     * all HTTP methods.
                     *
                    'route_name' => [
                        'actions' => [
                            'action' => [
                                'default' => boolean,
                                'GET' => boolean,
                                'POST' => boolean,
                                // etc.
                            ],
                        ],
                        'collection' => [
                            'default' => boolean,
                            'GET' => boolean,
                            'POST' => boolean,
                            // etc.
                        ],
                        'entity' => [
                            'default' => boolean,
                            'GET' => boolean,
                            'POST' => boolean,
                            // etc.
                        ],
                    ],
                     */
                ],
            ],
        ];
    }
}
