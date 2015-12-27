<?php

namespace Kcs\WatchdogBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SetPhpErrorsMonologHandlerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('kcs.watchdog.error_handler')) {
            return;
        }

        $definition = $container->getDefinition('monolog.logger.php');
        $definition
            ->addMethodCall('pushHandler', array(new Reference('kcs.watchdog.error_handler')));
    }
}
