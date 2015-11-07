<?php

namespace Kcs\WatchdogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorController extends Controller
{
    public function showAction($id)
    {
        $error = $this->get('kcs.watchdog.storage')->find($id);
        if ($error === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('KcsWatchdogBundle:Error:show.html.twig', array('error' => $error));
    }
}
