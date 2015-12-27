<?php

namespace Kcs\WatchdogBundle\Debug;

use Kcs\WatchdogBundle\Storage\StorageInterface;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * ExceptionHandler converts the exceptions passed from the ErrorHandler
 * and catched from the event listeners to an entity to be persisted
 *
 * NOTE: we need to inject the container to avoid a circular reference exception
 * getStorage and getTokenStorage methods are protected for testing purpose
 * and should not be overridden
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class ExceptionHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string[]
     */
    private $allowedExceptions;

    public function __construct(array $allowedExceptions)
    {
        $this->allowedExceptions = $allowedExceptions;
    }

    public function logException(\Exception $exception)
    {
        $class_ = get_class($exception);
        $level = E_ERROR;
        if($exception instanceof \ErrorException) {
            $level = $exception->getSeverity();
        }

        if (in_array($class_, $this->allowedExceptions)) {
            return;
        }

        $message = "{" . $class_ . "} " . $exception->getMessage();

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

    /**
     * Get the storage service
     *
     * @internal
     * @return StorageInterface
     */
    protected function getStorage()
    {
        return $this->container->get('kcs.watchdog.persister');
    }

    /**
     * Get the request token storage
     *
     * @internal
     * @return TokenStorage
     */
    protected function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }
}
