<?php

namespace Kcs\WatchdogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kcs_watchdog');

        $supportedDrivers = array('orm', 'couchdb');

        $rootNode
            ->children()
            ->scalarNode('db_driver')
                ->defaultValue('orm')
                ->validate()
                    ->ifNotInArray($supportedDrivers)
                    ->thenInvalid('%s is not a supported driver')
                ->end()
            ->end()
            ->arrayNode('allowed_exceptions')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('ignored_errors_path')
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('error_reporting_level')->defaultValue(-1)->end()
            ->scalarNode('document_manager')->defaultValue(null)->end()
            ->booleanNode('log_in_debug')->defaultFalse()->end()
        ->end();

        return $treeBuilder;
    }
}