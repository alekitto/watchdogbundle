<?php

namespace Kcs\WatchdogBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

use Kcs\WatchdogBundle\Debug\ErrorHandler;

class KcsWatchdogExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('kcs_watchdog.error_reporting_level', $config['error_reporting_level']);
        $container->setParameter('kcs_watchdog.allowed_exceptions', $config['allowed_exceptions']);
        $loader = new YamlFileLoader(
                $container,
                new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
    }
}
