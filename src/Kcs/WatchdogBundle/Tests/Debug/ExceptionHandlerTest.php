<?php

namespace Kcs\WatchdogBundle\Tests\Debug;

use Kcs\WatchdogBundle\Debug\ExceptionHandler;
use Kcs\WatchdogBundle\Storage\StorageInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExceptionHandlerTester extends ExceptionHandler
{
    private $storage;
    private $tokenStorage;

    protected function getStorage()
    {
        return $this->storage;
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    protected function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }
}

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowedExceptionsShouldNotBePersisted()
    {
        $storage = $this->prophesize('Kcs\WatchdogBundle\Storage\StorageInterface');
        $storage->persist(Argument::any())
            ->shouldNotBeCalled();
        $token = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $token->getToken()
            ->willReturn(null);

        $handler = new ExceptionHandlerTester(array("InvalidArgumentException"));
        $handler->setStorage($storage->reveal())->setTokenStorage($token->reveal());
        $handler->logException(new \InvalidArgumentException('TESTING'));
    }
}
