<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Devolicious\SilexTurboApiBundle\Resolver\ServiceControllerResolver;

/**
 * @author ruud
 */
class DIServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $file = $app['dir.cache'].'/container.php';
        $containerConfigCache = new ConfigCache($file, $app['debug']);

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();

            $containerBuilder->setParameter('dir.root', $app['dir.root']);

            $extensions = array();
            foreach ($extensions as $extension) {
                $containerBuilder->registerExtension($extension);
                $containerBuilder->loadFromExtension($extension->getAlias());
            }

            $containerBuilder->compile();
            
            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(array('class' => 'DICachedContainer')),
                $containerBuilder->getResources()
            );
        }

        require_once $file;
        $app['container'] = new \DICachedContainer();

        $app['resolver'] = $app->share($app->extend('resolver', function ($resolver, $app) {
            return new ServiceControllerResolver($resolver, $app);
        }));
    }

    public function boot(Application $app)
    {

    }
}