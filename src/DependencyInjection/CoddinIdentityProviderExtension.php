<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class CoddinIdentityProviderExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $afterAuthorizationRedirectUrl = 'coddin_identity_provider.account.profile';
        if (isset($config['auth']['without_client']['after_authorization_redirect_route_name'])) {
            $afterAuthorizationRedirectUrl = $config['auth']['without_client']['after_authorization_redirect_route_name'];
        }

        $container->setParameter('coddin_identity_provider.after_authorization_redirect_route_name', $afterAuthorizationRedirectUrl);

        $locator = new FileLocator(dirname(__FILE__) . '/../../config');
        $loader = new YamlFileLoader(
            container: $container,
            locator: $locator,
        );
        $loader->load('services.yaml');
    }
}
