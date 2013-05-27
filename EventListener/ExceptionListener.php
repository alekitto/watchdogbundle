<?php

namespace Kcs\WatchdogBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\DependencyInjection\Container;

use Kcs\WatchdogBundle\Debug\ExceptionHandler;

class ExceptionListener
{
    /**
     * Service Container
     * @var Container
     */
    private $container = null;

    public function __construct(Container $container) {
        $this->container = $container;
        ExceptionHandler::registerContainer($container);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $exceptionHandler = set_exception_handler(function() {});
        restore_exception_handler();

        if (is_array($exceptionHandler) && $exceptionHandler[0] instanceof ExceptionHandler)
        {
            $response = $exceptionHandler[0]->handle($exception,
                    $this->container->get('security.context')->getToken());
            if($response !== null) {
                $event->setResponse($response);
            }
        }
    }
}

