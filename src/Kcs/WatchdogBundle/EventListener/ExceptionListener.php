<?php

namespace Kcs\WatchdogBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Kcs\WatchdogBundle\Debug\ExceptionHandler;

class ExceptionListener
{
    const TYPE_DEPRECATION = -100;

    /**
     * Security Context
     * @var SecurityContext
     */
    private $context = null;

    /**
     * Doctrine Interface
     * @var Registry
     */
    private $doctrine = null;

    /**
     * Exception Handler
     * @var ExceptionHandler
     */
    private $handler = null;

    /**
     * Exceptions not to be logged
     * @var string[]
     */
    private $allowedExceptions = array();

    public function __construct(SecurityContext $context, Registry $doctrine,
            $debug, array $allowedExceptions) {
        $this->context = $context;
        $this->doctrine = $doctrine;
        $this->allowedExceptions = $allowedExceptions;

        // Initialize the exception handler
        $this->handler = new ExceptionHandler($debug);
        set_exception_handler(array($this->handler, 'handle'));
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $exception_class = get_class($exception);
        if(in_array($exception_class, $this->allowedExceptions))
            return;

        $exceptionHandler = set_exception_handler(function() {});
        restore_exception_handler();

        if (is_array($exceptionHandler) && $exceptionHandler[0] instanceof ExceptionHandler)
        {
            $response = $exceptionHandler[0]->handle($exception,
                    $this->doctrine, $this->context->getToken());
            if($response !== null) {
                $event->setResponse($response);
            }
        }
    }
}

