<?php

namespace Kcs\WatchdogBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

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
     * Error logger
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Exceptions not to be logged
     * @var string[]
     */
    private $allowedExceptions = array();

    /**
     * Error levels to string
     * @var array
     */
    public static $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        E_ERROR             => 'Error',
        E_CORE_ERROR        => 'Core Error',
        E_COMPILE_ERROR     => 'Compile Error',
        E_PARSE             => 'Parse',
    );

    /**
     * The errors below this level will be NOT reported
     * @var int
     */
    private $errorReportingLevel;

    private $reservedMemory;

    public function __construct(SecurityContext $context, StorageInterface $storage,
            LoggerInterface $logger, $debug, $errorLevel, array $allowedExceptions) {
        $this->context = $context;
        $this->storage = $storage;
        $this->errorReportingLevel = $errorLevel;
        $this->logger = $logger;
        $this->allowedExceptions = $allowedExceptions;

        // Initialize the exception handler
        $this->handler = new ExceptionHandler($debug);
        set_exception_handler(array($this->handler, 'handle'));

        // Now set the error and fatal handlers
        ini_set('display_errors', 0);
        set_error_handler(array($this, 'handleError'));
        register_shutdown_function(array($this, 'handleFatal'));

        /**
         * Reserve some memory:
         * In case of Memory Limit Exceeded this allows the error logging,
         * unsetting and freeing this block we can proceed normally
         */
        $this->reservedMemory = str_repeat('x', 10240);
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
                    $this->storage, $this->context->getToken());
            if($response !== null) {
                $event->setResponse($response);
            }
        }
    }

    /**
     * @throws \ErrorException When error_reporting returns error
     */
    public function handleError($level, $message, $file, $line, $context)
    {
        if (0 === $this->level) {
            return false;
        }

        if ($level & (E_USER_DEPRECATED | E_DEPRECATED)) {
            if (null !== $this->logger) {
                $stack = version_compare(PHP_VERSION, '5.4', '<') ? array_slice(debug_backtrace(false), 0, 10) : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

                $this->logger->warning($message, array('type' => self::TYPE_DEPRECATION, 'stack' => $stack));
            }

            return true;
        }

        if (error_reporting() & $level && $this->level & $level) {
            throw new \ErrorException(sprintf('%s: %s in %s line %d', isset(self::$levels[$level]) ? self::$levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);
        }

        return false;
    }

    public function handleFatal()
    {
        if (null === $error = error_get_last()) {
            return;
        }

        unset($this->reservedMemory);
        $type = $error['type'];
        if (0 === $this->level || !in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
            return;
        }

        // get current exception handler
        $exceptionHandler = set_exception_handler(function() {});
        restore_exception_handler();

        if (is_array($exceptionHandler) && $exceptionHandler[0] instanceof ExceptionHandler) {
            $level = isset(self::$levels[$type]) ? self::$levels[$type] : $type;
            $message = sprintf('%s: %s in %s line %d', $level, $error['message'], $error['file'], $error['line']);
            $exception = new FatalErrorException($message, 0, $type, $error['file'], $error['line']);
            if(($response = $exceptionHandler[0]->handle($exception, $this->storage,
                    $this->context ? $this->context->getToken() : null))) {
                $response->send();
            }
        }
    }
}

