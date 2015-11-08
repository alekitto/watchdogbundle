<?php

namespace Kcs\WatchdogBundle;

use Kcs\Doctrine\Types\Type;
use Kcs\WatchdogBundle\DependencyInjection\CompilerPass\DoctrinePersisterServicesGeneratorPass;
use Kcs\WatchdogBundle\DependencyInjection\CompilerPass\SetPhpErrorsMonologHandlerPass;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KcsWatchdogBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DoctrinePersisterServicesGeneratorPass())
            ->addCompilerPass(new SetPhpErrorsMonologHandlerPass())
            ;
    }
}
