<?php

namespace Kcs\WatchdogBundle\Debug;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Kcs\WatchdogBundle\Storage\StorageInterface;
use Kcs\WatchdogBundle\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * ErrorHandler registers itself as a PHP error handler.
 * When an error is triggered the handleError method catches it,
 * creates an exception object and passes it to the handler that will log it.
 * Additionally it registers the handleFatal method as a shutdown function
 * in order to catch and log an eventual fatal error
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
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

    public function __construct(
        SecurityContext $context,
        StorageInterface $storage,
        $debug,
        $errorLevel,
        array $ignored_path
        )
    {
        $this->context = $context;
        $this->storage = $storage;
        $this->errorReportingLevel = $errorLevel;
        $this->ignored_path = $ignored_path;
        $this->debug = $debug;

        // Now set the error and fatal handlers
        ini_set('display_errors', 0);
        $this->registerErrorHandler();
        $this->registerFatalHandler();

        /**
         * Reserve some memory:
         * In case of Memory Limit Exceeded this allows the error logging,
         * unsetting and freeing this block we can proceed normally
         */
        $this->reservedMemory = str_repeat('x', 10240);
    }

    /**
     * Register the PHP error handler
     */
    protected function registerErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
    }

    /**
     * Register a shutdown function in order to log eventual FATAL error
     */
    protected function registerFatalHandler()
    {
        register_shutdown_function(array($this, 'handleFatal'));
    }

    /**
     * Error handler
     * @throws \ErrorException When error_reporting returns error
     */
    public function handleError($level, $message, $file, $line, $context)
    {
        // Error logging disabled
        if (0 === $this->errorReportingLevel) {
            return false;
        }

        if (null === $this->handler) {
            // Initialize the exception handler
            $this->handler = new ExceptionHandler($this->debug);
        }

        // Is the error in a ignored file?
        foreach($this->ignored_path as $pathRegex) {
            // Build a path regex
            $pathRegex = '#' . str_replace('#', '\#', $pathRegex) . '#iu';
            if(preg_match($pathRegex, $file)) {
                // This path is ignored. Skip the logging
                return false;
            }
        }

        // Check if the error is loggable
        if ($this->errorReportingLevel & ($level | E_USER_DEPRECATED | E_DEPRECATED)) {
            $exception = new \ErrorException(sprintf('%s: %s in %s line %d', isset(self::$levels[$level]) ? self::$levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);
            $this->handler->handle($exception, $this->storage, $this->context ? $this->context->getToken() : null);
        }

        // Return true on deprecated errors, false otherwise to continue the event notification
        return (($level & E_USER_DEPRECATED | E_DEPRECATED) !== 0);
    }

    /**
     * Registered as a shutdown handler to eventually catch a fatal PHP error
     */
    public function handleFatal()
    {
        // Free reserved memory:
        // If no error is encountered, simply free up this memory region
        // otherwise we should need this memory in order to continue:
        // if, for example, if a memory limit exceeded error occoures
        // we need this space in order to continue and log the error
        unset($this->reservedMemory);

        if (null === $error = error_get_last()) {
            // No error encountered
            return;
        }

        $type = $error['type'];
        if (0 === $this->errorReportingLevel || !in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
            // Already logged by handleError function
            return;
        }

        if (null === $this->handler) {
            // Initialize the exception handler
            $this->handler = new ExceptionHandler($this->debug);
        }

        $level = isset(self::$levels[$type]) ? self::$levels[$type] : $type;
        $message = sprintf('%s: %s in %s line %d', $level, $error['message'], $error['file'], $error['line']);

        // Create the Exception object
        $exception = new FatalErrorException($message, 0, $type, $error['file'], $error['line']);
        $token = $this->context ? $this->context->getToken() : null;

        // Log the error
        $response = $this->handler->handle($exception, $this->storage, $token);
        if(null !== $response) {
            // If the handler produced a response object, send it to the client
            $response->send();
        }
    }

    public function init() { }
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'init');
    }
}

