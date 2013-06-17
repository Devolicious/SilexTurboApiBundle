<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Devolicious\SilexTurboApiBundle\Resolver\ServiceControllerResolver;
use Doctrine\Common\Collections\ArrayCollection;

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
            $containerBuilder->setParameter('dir.devolicious', __DIR__ . '/../../../');

            $extensions = new ArrayCollection();
            if (isset($app['api.extensions']) && is_array($app['api.extensions'])) {
                $extensions = new ArrayCollection($app['api.extensions']);
            }
            $extensions->add('\Devolicious\SilexTurboApiBundle\DependencyInjection\STABExtension');

            foreach ($extensions as $extension) {
                $class = new $extension();
                $containerBuilder->registerExtension($class);
                $containerBuilder->loadFromExtension($class->getAlias());
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