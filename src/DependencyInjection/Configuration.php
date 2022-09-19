<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('coddin_identity_provider');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('auth')
                    ->children()
                        ->arrayNode('without_client')
                            ->children()
                                ->scalarNode('after_authorization_redirect_route_name')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
