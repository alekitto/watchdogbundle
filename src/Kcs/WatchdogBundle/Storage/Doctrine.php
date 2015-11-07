<?php

namespace Kcs\WatchdogBundle\Storage;

use Doctrine\Common\Persistence\ObjectRepository;
use Kcs\WatchdogBundle\Entity\AbstractError;
use Symfony\Bridge\Doctrine\RegistryInterface;

class Doctrine implements StorageInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ObjectRepository
     */
    private $repository;

    public function __construct(RegistryInterface $registry, $objectClass)
    {
        $this->om = $registry->getManagerForClass($objectClass);
        $this->repository = $this->om->getRepository($objectClass);
    }

    /**
     * @inheritDoc
     */
    public function getNewEntity()
    {
        $reflClass = new \ReflectionClass($this->repository->getClassName());
        return $reflClass->newInstance();
    }

    /**
     * @inheritDoc
     */
    public function persist(AbstractError $error)
    {
        $this->om->persist($error);
        $this->om->flush([$error]);
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }
}
