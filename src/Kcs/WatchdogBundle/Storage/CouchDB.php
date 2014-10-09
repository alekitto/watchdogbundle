<?php

namespace Kcs\WatchdogBundle\Storage;

use Kcs\WatchdogBundle\Entity\AbstractError;
use Kcs\WatchdogBundle\CouchDocument\Error;
use Doctrine\ODM\CouchDB\DocumentManager;

/**
 * CouchDB watchdog storage class
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class CouchDB implements StorageInterface
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function getNewEntity()
    {
        return new Error;
    }

    public function persist(AbstractError $error)
    {
        $this->documentManager->persist($error);
        $this->documentManager->flush();
    }

    public function find($id)
    {
        return $this->documentManager->find('KcsWatchdogBundle:Error', $id);
    }
}

