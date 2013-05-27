<?php

namespace Kcs\WatchdogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Kcs\WatchdogBundle\Debug\ErrorHandler;
use Kcs\WatchdogBundle\Debug\ExceptionHandler;

use Doctrine\DBAL\Connection;

class KcsWatchdogBundle extends Bundle
{
    /**
     * Listener registered default connection
     * @var Connection
     */
    protected static $defaultConnection = null;

    public static function register($debug, $errorReportingLevel = null)
    {
        error_reporting(-1);
        ErrorHandler::register($errorReportingLevel);
        ExceptionHandler::register($debug);
    }

    public static function setDefaultConnection(Connection $defaultConnection)
    {
        self::$defaultConnection = $defaultConnection;
    }

    /**
     * Return the default connection
     * @return Connection
     */
    public static function getDefaultConnection()
    {
      return self::$defaultConnection;
    }
}
