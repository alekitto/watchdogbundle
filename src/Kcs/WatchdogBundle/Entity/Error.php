<?php

namespace Kcs\WatchdogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="watchdog")
 */
class Error
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $level;

    /**
     * @ORM\Column(type="string", length=1020)
     */
    protected $message;

    /**
     * @ORM\Column(type="string", length=510)
     */
    protected $file;

    /**
     * @ORM\Column(type="integer")
     */
    protected $line;

    /**
     * @ORM\Column(type="text")
     */
    protected $trace;

    /**
     * @ORM\Column(type="text")
     */
    protected $variables;

    /**
     * @ORM\Column(type="text")
     */
    protected $user;

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
     * @return Error
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
     * @return Error
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
     * @return Error
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
     * @return Error
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
     * @param string $trace
     * @return Error
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
    
        return $this;
    }

    /**
     * Get trace
     *
     * @return string 
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Set variables
     *
     * @param string $variables
     * @return Error
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    
        return $this;
    }

    /**
     * Get variables
     *
     * @return string 
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return Error
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }
}