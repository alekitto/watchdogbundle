<?php

namespace Kcs\WatchdogBundle\Storage;

use Kcs\WatchdogBundle\Entity\AbstractError;
use Kcs\WatchdogBundle\Doctrine\Error;
use Doctrine\Bundle\DoctrineBundle\Registry;

class Doctrine implements StorageInterface
{
    /**
     * @var Registry
     */
    protected $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getNewEntity()
    {
        return new Error;
    }

    public function persist(AbstractError $error)
    {
        $em = $this->doctrine->getManager();
        $em->persist($error);
        $em->flush();
    }
}

