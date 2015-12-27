<?php

namespace Kcs\WatchdogBundle\Tests\DependencyInjection\CompilerPass;

use Kcs\WatchdogBundle\DependencyInjection\CompilerPass\DoctrinePersisterServicesGeneratorPass;
use Prophecy\Argument;

class DoctrinePersisterServicesGeneratorPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSkipIfDoctrinePersisterDefinitionIsNotPresent()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->hasDefinition('kcs.watchdog.persister.doctrine')->willReturn(false);
        $container->setDefinition(Argument::cetera())->shouldNotBeCalled();

        $pass = new DoctrinePersisterServicesGeneratorPass();
        $pass->process($container->reveal());
    }
}
