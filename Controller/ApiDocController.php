<?php

/*
 * This file is an part of the Devolicious\SilexTurboApiBundle.
 *
 * (c) Devolicious <ruud@devolution.be>
 *
 */

namespace Devolicious\SilexTurboApiBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

class ApiDocController
{
    public function indexAction(Application $app)
    {
        $data = $app['container']->get('stab.extractor.apidoc')->getData($app['api.name'], $app['routes']);
        return $app['twig']->render('api-docs.html.twig', $data);
    }
}
