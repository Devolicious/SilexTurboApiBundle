<?php

namespace Devolicious\SilexTurboApiBundle\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Devolicious\SilexTurboApiBundle\Annotation\Secure;
use Devolicious\SilexTurboApiBundle\Security\Provider\ApiProvider;
use Devolicious\SilexTurboApiBundle\Security\Provider\UserProvider;
use Devolicious\SilexTurboApiBundle\Security\Firewall\ApiListener;
use Devolicious\SilexTurboApiBundle\Security\Encoder\ApiEncoder;


class SecurityServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerFirewall($app);
        $this->checkUserRoles($app);
    }

    public function boot(Application $app)
    {

    }

    public function registerFirewall(Application $app)
    {
        $app->register(new \Silex\Provider\SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'wsse_secured' => array(
                    'pattern' => '^/api/(?!doc).*',
                    'security' => $app['api.security.enabled'] === false ? false : true,
                    'stateless' => true,
                    'wsse' => true,
                    'users' => $app->share(function () use ($app) {
                        return new UserProvider($app['db']);
                    }),
                ),
            )

        ));

        $app['security.encoder.digest'] = $app->share(function ($app) {
            return new ApiEncoder();
        });

        $app['security.authentication_listener.factory.wsse'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.wsse'] = $app->share(function () use ($app) {
                return new ApiProvider($app['security.user_provider.wsse_secured'], $app['security.encoder.digest']);
            });

            $app['security.authentication_listener.'.$name.'.wsse'] = $app->share(function () use ($app) {
                return new ApiListener($app['security'], $app['security.authentication_manager']);
            });

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.wsse',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.wsse',
                // the entry point id
                null,
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }

    public function checkUserRoles(Application $app)
    {
        $app->before(function(Request $request) use ($app){
            list($controller, $method) = explode(':', $request->get('_controller'));
            $reader = new AnnotationReader();

            if (isset($app['container']) && $app['container']->has($controller)) {
                $controller = $app['container']->get($controller);
            }

            $refl = new \ReflectionMethod($controller, $method . 'Action');
            $secure = $reader->getMethodAnnotation($refl, 'Devolicious\\SilexTurboApiBundle\\Annotation\\Secure');

            if (!$secure instanceof Secure) {
                return;
            }

            $roles = $secure->getRoles();
            if(!$app['security']->isGranted($roles)){
                throw new AccessDeniedException();
            }
        });
    }
}