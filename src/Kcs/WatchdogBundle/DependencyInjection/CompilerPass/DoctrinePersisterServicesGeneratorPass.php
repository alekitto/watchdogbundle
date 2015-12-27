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
        if (! $container->hasDefinition('kcs.watchdog.persister.doctrine')) {
            return;
        }

        $this->addDoctrineORMDefinition($container);
        $this->addDoctrineCouchDefinition($container);
    }

    private function addDoctrineORMDefinition(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('doctrine')) {
            return;
        }

        $definition = $container->getDefinition('kcs.watchdog.persister.doctrine');

        $orm = clone $definition;
        $orm->replaceArgument(0, new Reference('doctrine'));
        $orm->replaceArgument(1, 'Kcs\WatchdogBundle\Entity\Error');
        $orm->setAbstract(false);
        $container->setDefinition('kcs.watchdog.persister.doctrine.orm', $orm);
    }

    private function addDoctrineCouchDefinition(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('doctrine_couchdb')) {
            return;
        }

        $definition = $container->getDefinition('kcs.watchdog.persister.doctrine');

        $couchdb = clone $definition;
        $couchdb->replaceArgument(0, new Reference('doctrine_couchdb'));
        $couchdb->replaceArgument(1, 'Kcs\WatchdogBundle\CouchDocument\Error');
        $couchdb->setAbstract(false);
        $container->setDefinition('kcs.watchdog.persister.doctrine.couchdb', $couchdb);
    }
}
