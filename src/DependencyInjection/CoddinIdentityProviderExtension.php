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
        $locator = new FileLocator(dirname(__FILE__) . '/../../config');
        $loader = new YamlFileLoader(
            container: $container,
            locator: $locator,
        );
        $loader->load('services.yaml');
    }
}
