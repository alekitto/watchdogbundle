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

        $rootNode
            ->children()
            ->scalarNode('error_reporting_level')->defaultValue(-1)->end()
            ->end();

        return $treeBuilder;
    }
}