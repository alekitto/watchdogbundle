<?php

namespace Kcs\WatchdogBundle\Tests\Debug;

use Kcs\WatchdogBundle\Debug\ExceptionHandler;
use Kcs\WatchdogBundle\Entity\Error;
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

    public function testLogException()
    {
        $storage = $this->prophesize('Kcs\WatchdogBundle\Storage\StorageInterface');
        $storage->getNewEntity()->willReturn(new Error());
        $storage->persist(Argument::type('Kcs\WatchdogBundle\Entity\AbstractError'))
            ->shouldBeCalledTimes(1);
        $token = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $token->getToken()
            ->willReturn(null);

        $handler = new ExceptionHandlerTester(array());
        $handler->setStorage($storage->reveal())->setTokenStorage($token->reveal());
        $handler->logException(new \InvalidArgumentException('TESTING'));
    }

    public function testLogExceptionLogsUserCorrectly()
    {
        $that = $this;
        $storage = $this->prophesize('Kcs\WatchdogBundle\Storage\StorageInterface');
        $storage->getNewEntity()->willReturn(new Error());
        $storage->persist(Argument::type('Kcs\WatchdogBundle\Entity\AbstractError'))
            ->shouldBeCalledTimes(1)
            ->will(function($arguments) use ($that) {
                $user = $arguments[0]->getUser();
                $that->assertEquals('user_test', $user['username']);
            });

        $token = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->getUsername()->willReturn('user_test');
        $token->getAttributes()->willReturn(array('attr' => 'test'));

        $tokenStorage = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorage->getToken()->willReturn($token->reveal());

        $handler = new ExceptionHandlerTester(array());
        $handler->setStorage($storage->reveal())->setTokenStorage($tokenStorage->reveal());
        $handler->logException(new \InvalidArgumentException('TESTING'));
    }

    public function testLogExceptionShouldSetSeverityOnErrorException()
    {
        $that = $this;
        $exc = new \ErrorException('TEST', 0, E_USER_ERROR);

        $storage = $this->prophesize('Kcs\WatchdogBundle\Storage\StorageInterface');
        $storage->getNewEntity()->willReturn(new Error());
        $storage->persist(Argument::type('Kcs\WatchdogBundle\Entity\AbstractError'))
            ->shouldBeCalledTimes(1)
            ->will(function($arguments) use ($that) {
                $that->assertEquals(E_USER_ERROR, $arguments[0]->getLevel());
            });

        $token = $this->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $token->getToken()
            ->willReturn(null);

        $handler = new ExceptionHandlerTester(array());
        $handler->setStorage($storage->reveal())->setTokenStorage($token->reveal());
        $handler->logException($exc);
    }
}
