<?php

namespace Kcs\WatchdogBundle;

use Kcs\Doctrine\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KcsWatchdogBundle extends Bundle
{
    public function boot()
    {
        // Ensure binary array doctrine type is present
        Type::registerTypes();
    }
}
