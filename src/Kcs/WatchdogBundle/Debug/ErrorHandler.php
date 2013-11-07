<?php

namespace Kcs\WatchdogBundle\Debug;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Kcs\WatchdogBundle\Storage\StorageInterface;
use Kcs\WatchdogBundle\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalErrorException;

class ErrorHandler implements EventSubscriberInterface
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
     * Kernel debug flag
     * @var boolean
     */
    private $debug = false;

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

    /**
     * Errors from files contained in these paths are ignored (if not fatal)
     * @var array
     */
    private $ignored_path = array();

    private $reservedMemory;

    public function __construct(SecurityContext $context, StorageInterface $storage,
            $debug, $errorLevel, array $ignored_path) {
        $this->context = $context;
        $this->storage = $storage;
        $this->errorReportingLevel = $errorLevel;
        $this->ignored_path = $ignored_path;
        $this->debug = $debug;

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

    /**
     * @throws \ErrorException When error_reporting returns error
     */
    public function handleError($level, $message, $file, $line, $context)
    {
        if (0 === $this->errorReportingLevel) {
            return false;
        }

        if (null === $this->handler) {
            // Initialize the exception handler
            $this->handler = new ExceptionHandler($this->debug);
        }

        if ($level & (E_USER_DEPRECATED | E_DEPRECATED)) {
            $deprecated = new \ErrorException(sprintf('%s: %s in %s line %d', isset(self::$levels[$level]) ? self::$levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);
            $this->handler->handle($deprecated, $this->storage, $this->context ? $this->context->getToken() : null);
            return true;
        }

        foreach($this->ignored_path as $pathRegex)
        {
            $pathRegex = '#' . str_replace('#', '\#', $pathRegex) . '#iu';
            if(preg_match($pathRegex, $file)) {
                return false;
            }
        }

        if ($this->errorReportingLevel & $level) {
            $exception = new \ErrorException(sprintf('%s: %s in %s line %d', isset(self::$levels[$level]) ? self::$levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);
            $this->handler->handle($exception, $this->storage, $this->context ? $this->context->getToken() : null);
        }

        return false;
    }

    public function handleFatal()
    {
        unset($this->reservedMemory);
        if (null === $error = error_get_last()) {
            return;
        }

        $type = $error['type'];
        if (0 === $this->errorReportingLevel || !in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
            return;
        }

        $level = isset(self::$levels[$type]) ? self::$levels[$type] : $type;
        $message = sprintf('%s: %s in %s line %d', $level, $error['message'], $error['file'], $error['line']);
        $exception = new FatalErrorException($message, 0, $type, $error['file'], $error['line']);
        if(($response = $this->handler->handle($exception, $this->storage,
                $this->context ? $this->context->getToken() : null))) {
            $response->send();
        }
    }

    public function init() { }
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'init');
    }
}

