<?php

namespace Kcs\WatchdogBundle\Debug;

use Monolog\Handler\AbstractProcessingHandler;
use Kcs\WatchdogBundle\Storage\StorageInterface;
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
class ErrorHandler extends AbstractProcessingHandler
{
    /**
     * @var ExceptionHandler
     */
    private $handler;

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

    /**
     * Reserved memory to free at shutdown
     */
    private static $reservedMemory;

    /**
     * @var self
     */
    private static $fatalErrorHandler;

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

    public function __construct(ExceptionHandler $handler, $errorLevel, array $ignored_path)
    {
        $this->handler = $handler;
        $this->errorReportingLevel = $errorLevel;
        $this->ignored_path = $ignored_path;

        self::$fatalErrorHandler = $this;
    }

    public static function registerFatalErrorHandler()
    {
        self::$reservedMemory = str_repeat('x', 10240);
        register_shutdown_function(__CLASS__ . '::handleFatal');
    }

    /**
     * @inheritDoc
     */
    protected function write(array $record)
    {
        // Error logging disabled
        if (0 === $this->errorReportingLevel) {
            return;
        }

        set_error_handler(function() {}, E_ALL);

        extract($record['context'] + array ('type' => E_ERROR, 'file' => 'unknown file', 'line' => 0));
        $message = $record['message'];

        restore_error_handler();

        // Check if the error is loggable
        if (($this->errorReportingLevel & $type) === 0) {
            return;
        }

        // Is the error in a ignored file?
        foreach($this->ignored_path as $pathRegex) {
            // Build a path regex
            $pathRegex = '#' . str_replace('#', '\#', $pathRegex) . '#iu';
            if(preg_match($pathRegex, $file)) {
                // This path is ignored. Skip the logging
                return;
            }
        }

        $exception = new \ErrorException(sprintf('%s: %s in %s line %d', isset(self::$levels[$type]) ? self::$levels[$type] : $type, $message, $file, $line), 0, $type, $file, $line);
        $this->handler->logException($exception);
    }

    /**
     * Registered as a shutdown handler to eventually catch a fatal PHP error
     */
    public static function handleFatal()
    {
        // Free reserved memory:
        // If no error is encountered, simply free up this memory region
        // otherwise we should need this memory in order to continue:
        // if, for example, if a memory limit exceeded error occoures
        // we need this space in order to continue and log the error
        unset(self::$reservedMemory);
        self::$reservedMemory = null;

        $handler = self::$fatalErrorHandler;
        if (!$handler instanceof self) {
            return;
        }

        // We need this for testing purpose
        $args = func_get_args();
        if (isset($args[0])) {
            $error = $args[0];
        } else {
            // @codeCoverageIgnoreStart
            $error = error_get_last();
            // @codeCoverageIgnoreEnd
        }

        if (null === $error) {
            // No error encountered
            return;
        }

        $type = $error['type'];
        if (0 === $handler->errorReportingLevel || !in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
            // Already logged by handleError function
            return;
        }

        $level = isset(self::$levels[$type]) ? self::$levels[$type] : $type;
        $message = sprintf('%s: %s in %s line %d', $level, $error['message'], $error['file'], $error['line']);

        // Create the Exception object
        $exception = new FatalErrorException($message, 0, $type, $error['file'], $error['line']);

        // Log the error
        $handler->handler->logException($exception);
    }
}

