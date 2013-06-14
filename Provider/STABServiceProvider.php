<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->register(new RoutingServiceProvider());
    }

    public function boot(Application $app)
    {}
}