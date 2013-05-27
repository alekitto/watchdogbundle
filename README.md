## KCS Watchdog bundle for Symfony2

### Requirements:

* Symfony2
* Doctrine 2

### Installation:

* Clone this repository into src/ folder of your symfony2 project
* Override your AppKernel::init() function with this:

    public function init()
    {
        parent::init();
        Kcs\WatchdogBundle\KcsWatchdogBundle::register($this->debug);
    }

* Create the watchdog table on your database
* Enjoy!
