<?php

declare(strict_types=1);

namespace Lmc\Api\Auth\Authorization;

use Psr\Container\ContainerInterface;

use function array_key_exists;
use function array_keys;
use function is_array;
use function lcfirst;
use function sprintf;

class AclAuthorizationFactory
{
    protected array $httpMethods = [
        'DELETE' => true,
        'GET'    => true,
        'PATCH'  => true,
        'POST'   => true,
        'PUT'    => true,
    ];

    public function __invoke(ContainerInterface $container): AclAuthorization
    {
        /** @var array $config */
        $config = $container->get('config');
        $config = $config['lmc_api']['authorization']['authorization'];
        return $this->createAclFromConfig($config);
    }

    private function createAclFromConfig(mixed $config): AclAuthorization
    {
        $aclConfig     = [];
        $denyByDefault = false;

        if (array_key_exists('deny_by_default', $config)) {
            $denyByDefault = $aclConfig['deny_by_default'] = (bool) $config['deny_by_default'];
            unset($config['deny_by_default']);
        }

        foreach ($config as $routeName => $privileges) {
            $this->createAclConfigFromPrivileges($routeName, $privileges, $aclConfig, $denyByDefault);
        }

        return $this->createAclInstance($aclConfig);
    }

    private function createAclConfigFromPrivileges(
        string $routeName,
        array $privileges,
        array &$aclConfig,
        bool $denyByDefault
    ): void {
        if (isset($privileges['actions'])) {
            foreach ($privileges['actions'] as $action => $methods) {
                $action      = lcfirst($action);
                $aclConfig[] = [
                    'resource'   => sprintf('%s::%s', $routeName, $action),
                    'privileges' => $this->createPrivilegesFromMethods($methods, $denyByDefault),
                ];
            }
        }

        if (isset($privileges['collection'])) {
            $aclConfig[] = [
                'resource'   => sprintf('%s::collection', $routeName),
                'privileges' => $this->createPrivilegesFromMethods($privileges['collection'], $denyByDefault),
            ];
        }

        if (isset($privileges['entity'])) {
            $aclConfig[] = [
                'resource'   => sprintf('%s::entity', $routeName),
                'privileges' => $this->createPrivilegesFromMethods($privileges['entity'], $denyByDefault),
            ];
        }
    }

    private function createPrivilegesFromMethods(array $methods, bool $denyByDefault): array|null
    {
        $privileges = [];

        if (isset($methods['default']) && $methods['default']) {
            $privileges = $this->httpMethods;
            unset($methods['default']);
        }

        foreach ($methods as $method => $flag) {
            // If the flag evaluates true, and we're denying by default, OR
            // if the flag evaluates false, and we're allowing by default,
            // THEN no rule needs to be added
            if (
                ( $denyByDefault && $flag)
                || (! $denyByDefault && ! $flag)
            ) {
                if (isset($privileges[$method])) {
                    unset($privileges[$method]);
                }
                continue;
            }

            // Otherwise, we need to add a rule
            $privileges[$method] = true;
        }

        if (empty($privileges)) {
            return null;
        }

        return array_keys($privileges);
    }

    private function createAclInstance(array $config): AclAuthorization
    {
        // Determine whether we are whitelisting or blacklisting
        $denyByDefault = false;
        if (array_key_exists('deny_by_default', $config)) {
            $denyByDefault = (bool) $config['deny_by_default'];
            unset($config['deny_by_default']);
        }

        // By default, create an open ACL
        $acl = new AclAuthorization();
        $acl->addRole('guest');
        $acl->allow();

        $grant = 'deny';
        if ($denyByDefault) {
            $acl->deny('guest', null, null);
            $grant = 'allow';
        }

        if (! empty($config)) {
            return $this->injectGrants($acl, $grant, $config);
        }

        return $acl;
    }

    private function injectGrants(AclAuthorization $acl, string $grantType, array $rules): AclAuthorization
    {
        foreach ($rules as $set) {
            if (! is_array($set) || ! isset($set['resource'])) {
                continue;
            }

            $this->injectGrant($acl, $grantType, $set);
        }

        return $acl;
    }

    private function injectGrant(AclAuthorization $acl, string $grantType, array $ruleSet): void
    {
        // Add new resource to ACL
        $resource = $ruleSet['resource'];
        $acl->addResource($ruleSet['resource']);

        // Deny guest specified privileges to resource
        $privileges = $ruleSet['privileges'] ?? null;

        // null privileges means no permissions were setup; nothing to do
        if (null === $privileges) {
            return;
        }
        $acl->$grantType('guest', $resource, $privileges);
    }
}
