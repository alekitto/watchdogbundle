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
     * @var \DateTime
     */
    protected $date;

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

    public function __construct()
    {
        $this->date = new \DateTime;
    }

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
     * Set date
     *
     * @param \DateTime $date
     * @return AbstractError
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
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

    /**
     * Get error severity
     *
     * could be 'critical', 'warning', 'notice'
     */
    public function getSeverity()
    {
        switch($this->getLevel())
        {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
            case E_PARSE:
                return 'critical';

            case E_WARNING:
            case E_USER_WARNING:
            case E_STRICT:
                return 'warning';

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'notice';

            default:
                return 'unknown';
        }
    }
}
