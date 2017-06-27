<?php

namespace Mellivora\Support\Providers;

use Mellivora\Session\Session;
use Mellivora\Support\Providers\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['session'] = function ($container) {
            $handler = null;
            if ($config = $container['config']->get('session.saveHandler')) {
                dd(class_exists($config->handler));
            }

            $session = new Session($handler);

            $session->start();

            return $session;
        };
    }
}
