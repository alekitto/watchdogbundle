<?php

namespace Kcs\WatchdogBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContext;

use Kcs\WatchdogBundle\Storage\StorageInterface;
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
     * Entity Storage Interface
     * @var StorageInterface
     */
    private $storage = null;

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

    public function __construct(SecurityContext $context, StorageInterface $storage,
            $debug, array $allowedExceptions) {
        $this->context = $context;
        $this->storage = $storage;
        $this->allowedExceptions = $allowedExceptions;

        // Initialize the exception handler
        $this->handler = new ExceptionHandler($debug);
        set_exception_handler(array($this, 'handleException'));
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $exception_class = get_class($exception);
        if(in_array($exception_class, $this->allowedExceptions))
            return;

        $response = $this->handler->handle($exception, $this->storage, $this->context->getToken());
        if($response !== null) {
            $event->setResponse($response);
        }
    }

    public function handleException(\Exception $exception)
    {
      $this->handler->handle($exception, $this->storage, $this->context->getToken());
    }
}

