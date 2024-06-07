<?php

namespace TARGOBANK\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class TARGOBANKRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get('targobank/return', 'TARGOBANK\Controllers\ReturnController@handleReturn');
    }
}
