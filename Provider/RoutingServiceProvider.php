<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

class RoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['routes'] = $app->extend('routes', function (RouteCollection $routes) use ($app) {
            $loader = new YamlFileLoader(new FileLocator($app['dir.config']));
            $collection = $loader->load('routing.yml');
            
            $route = $collection->get('devolicious_apidoc');
            if ($route !== null) {
                $path = $route->getPath();
                $route->setPath(trim($path, '/'));
                $collection->add('devolicious_apidoc', $route);
            }
            
            $routes->addCollection($collection);
            return $routes;
        });
    }

    public function boot(Application $app)
    {}
}