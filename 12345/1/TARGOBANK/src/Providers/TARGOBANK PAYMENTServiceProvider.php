<?php

namespace TARGOBANK\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

class TARGOBANKServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot(Twig $twig)
    {
        $twig->addGlobal('targobankForm', pluginApp(\TARGOBANK\Services\HashService::class));
    }
}
