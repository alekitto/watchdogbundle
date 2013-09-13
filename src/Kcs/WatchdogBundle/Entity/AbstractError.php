<?php

namespace Kcs\WatchdogBundle\Entity;

/**
 * Storage-agnostic watchdog error class
 */
abstract class AbstractError
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var integer
     */
    protected $line;

    /**
     * @var array|mixed
     */
    protected $trace;

    /**
     * @var array|mixed
     */
    protected $variables;

    /**
     * @var array|mixed
     */
    protected $user;

    /**
     * @var boolean
     */
    protected $read = false;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return AbstractError
     */
    public function setLevel($level)
    {
        $this->level = $level;
    
        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return AbstractError
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return AbstractError
     */
    public function setFile($file)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set line
     *
     * @param integer $line
     * @return AbstractError
     */
    public function setLine($line)
    {
        $this->line = $line;
    
        return $this;
    }

    /**
     * Get line
     *
     * @return integer 
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Set trace
     *
     * @param array $trace
     * @return AbstractError
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
    
        return $this;
    }

    /**
     * Get trace
     *
     * @return array 
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Set variables
     *
     * @param array $variables
     * @return AbstractError
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    
        return $this;
    }

    /**
     * Get variables
     *
     * @return array 
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set user
     *
     * @param array $user
     * @return AbstractError
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return array 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return AbstractError
     */
    public function setRead($read)
    {
        $this->read = $read;
    
        return $this;
    }

    /**
     * Get read
     *
     * @return boolean 
     */
    public function getRead()
    {
        return $this->read;
    }
}