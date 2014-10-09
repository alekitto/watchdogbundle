<?php

namespace Kcs\WatchdogBundle\Storage;
use Kcs\WatchdogBundle\Entity\AbstractError;

interface StorageInterface
{
    /**
     * Get a new Error entity for the current storage driver
     * @return AbstractError A new abstract error entity
     */
    public function getNewEntity();

    /**
     * Persist the Error entity
     * @param AbstractError $error
     */
    public function persist(AbstractError $error);

    /**
     * Retrieve an error from storage driver
     *
     * @param $id
     * @return AbstractError
     */
    public function find($id);
}
