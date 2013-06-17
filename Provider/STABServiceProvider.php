<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class STABServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->register(new RoutingServiceProvider());
        $app->register(new DIServiceProvider());

        $app->register(new Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array(__DIR__ . '/../Resources/views/')
        ));

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            $twig->addExtension(new \Nelmio\ApiDocBundle\Twig\Extension\MarkdownExtension());

            return $twig;
        }));

        $app['container']->get('stab.annotation.loader')->registerAnnotations();
    }

    public function boot(Application $app)
    {}
}