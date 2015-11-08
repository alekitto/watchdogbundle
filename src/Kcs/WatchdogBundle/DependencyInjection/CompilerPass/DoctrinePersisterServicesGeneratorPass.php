<?php

namespace Kcs\WatchdogBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePersisterServicesGeneratorPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('kcs.watchdog.persister.doctrine')) {
            return;
        }

        $definition = $container->getDefinition('kcs.watchdog.persister.doctrine');

        $orm = clone $definition;
        $orm->replaceArgument(1, 'Kcs\WatchdogBundle\Entity\Error');
        $orm->setAbstract(false);
        $container->setDefinition('kcs.watchdog.persister.doctrine.orm', $orm);

        $couchdb = clone $definition;
        $couchdb->replaceArgument(0, new Reference('doctrine_couchdb'));
        $couchdb->replaceArgument(1, 'Kcs\WatchdogBundle\CouchDocument\Error');
        $orm->setAbstract(false);
        $container->setDefinition('kcs.watchdog.persister.doctrine.couchdb', $orm);
    }
}
