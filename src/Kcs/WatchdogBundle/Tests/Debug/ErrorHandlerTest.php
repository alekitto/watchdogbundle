<?php

namespace Kcs\WatchdogBundle\Tests\Debug;

use Kcs\WatchdogBundle\Debug\ErrorHandler;
use Prophecy\Argument;

class ErrorHandlerWriterTest extends ErrorHandler
{
    /**
     * @inheritDoc
     */
    public function handle(array $record)
    {
        $this->write($record);
        return true;
    }
}

/**
 * @covers \Kcs\WatchdogBundle\Debug\ErrorHandler
 */
class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function invalidErrorReportingLevelDataProvider()
    {
        return array (
            array (0, E_ERROR),
            array (E_ERROR | E_WARNING, E_NOTICE),
            array (E_ALL & ~(E_USER_DEPRECATED), E_USER_DEPRECATED)
        );
    }

    /**
     * @dataProvider invalidErrorReportingLevelDataProvider
     */
    public function testShouldNotLogAnythingIfErrorReportingLevelIsInvalid($error_reporting, $error_level)
    {
        $exceptionHandler = $this->prophesize('Kcs\WatchdogBundle\Debug\ExceptionHandler');
        $exceptionHandler->logException(Argument::cetera())->shouldNotBeCalled();

        $handler = new ErrorHandlerWriterTest($exceptionHandler->reveal(), $error_reporting, array ());
        $handler->handle(array (
            'message' => 'Not intresting',
            'context' => array(
                'type' => $error_level,
            )
        ));
    }

    public function testShouldNotLogAnythingIfPathIsIgnored()
    {
        $exceptionHandler = $this->prophesize('Kcs\WatchdogBundle\Debug\ExceptionHandler');
        $exceptionHandler->logException(Argument::cetera())->shouldNotBeCalled();

        $handler = new ErrorHandlerWriterTest($exceptionHandler->reveal(), E_ALL, array (
            '^/var/www/project/ignored/.+$'
        ));
        $handler->handle(array (
            'message' => 'Not intresting',
            'context' => array(
                'type' => E_ERROR,
                'file' => '/var/www/project/ignored/IgnoredErrorClass.php'
            )
        ));
    }

    public function testWrite()
    {
        $that = $this;
        $exceptionHandler = $this->prophesize('Kcs\WatchdogBundle\Debug\ExceptionHandler');
        $exceptionHandler->logException(Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->will(function(array $args) use ($that) {
                $that->assertInstanceOf('ErrorException', $args[0]);
            })
        ;

        $handler = new ErrorHandlerWriterTest($exceptionHandler->reveal(), E_ALL, array (
            '^/var/www/project/ignored/.+$'
        ));
        $handler->handle(array (
            'message' => 'Not intresting',
            'context' => array(
                'type' => E_ERROR,
                'file' => '/var/www/project/src/ErrorRaisingClass.php',
                'line' => 151
            )
        ));
    }
}
