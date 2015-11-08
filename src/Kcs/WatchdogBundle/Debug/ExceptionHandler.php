<?php

namespace Kcs\WatchdogBundle\Debug;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * ExceptionHandler converts the exceptions passed from the ErrorHandler
 * and catched from the event listeners to an entity to be persisted
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class ExceptionHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function logException(\Exception $exception)
    {
        $level = E_ERROR;
        if($exception instanceof \ErrorException) {
            $level = $exception->getSeverity();
        }

        $message = "{" . get_class($exception) . "} " . $exception->getMessage();

        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        $variables = array(
            'SERVER'           => $_SERVER,
            'GET'              => isset($_GET) ? $_GET : array(),
            'POST'             => isset($_POST) ? $_POST : array(),
            'COOKIES'          => isset($_COOKIE) ? $_COOKIE : array(),
            'ENV'              => isset($_ENV) ? $_ENV : array(),
            'FILES'            => isset($_FILES) ? $_FILES : array(),
            'Request Headers'  => function_exists('apache_request_headers') ? apache_request_headers() : array(),
            'Response Headers' => function_exists('apache_response_headers') ? apache_response_headers() : array(),
        );

        $user = array();
        if(null !== ($token = $this->getTokenStorage()->getToken())) {
            $user['username'] = $token->getUsername();
            $user['attributes'] = $token->getAttributes();
        }

        $storage = $this->getStorage();

        $error = $storage->getNewEntity();
        $error->setLevel($level)
              ->setMessage($message)
              ->setFile($exception->getFile())
              ->setLine($exception->getLine())
              ->setTrace($exception->getTrace())
              ->setVariables($variables)
              ->setUser($user)
        ;

        $storage->persist($error);
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $this->logException($event->getException());
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->logException($event->getException());
    }

    private function getStorage()
    {
        return $this->container->get('kcs.watchdog.persister');
    }

    private function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }
}
