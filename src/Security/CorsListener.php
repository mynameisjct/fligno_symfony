<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CorsListener{
    
    public function onKernelResponse(FilterResponseEvent $filterEvent){
        $responseHeaders = $filterEvent->getResponse()->headers;
        $responseHeaders->set("Access-Control-Allow-Headers", "origin","Content-Type", "accept");
        $responseHeaders->set("Access-Control-Allow-Origin","*");
        $responseHeaders->set("Access-Control-Allow-Methods", "POST, GET, DELETE, PUT, PATCH, OPTIONS");
    }
}