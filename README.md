SilexTurboApiBundle
===================

This bundle consists of 4 distinct pieces of software, NelmioApiDoc, Standard implementation of SF2 Security component, YAML based routing and SF2 Dependency Injection Container. You can install it with composer
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Devolicious/SilexTurboApiBundle"
        }
    ],
    "require": {
       "devolicious/silex-turbo-api-bundle": "~0.2",
    }
}

```
or you can just install it from the zip into `vendor/devolicious/silex-turbo-api-bundle/Devolicious/SilexTurboApiBundle/`.

## Configuration
To configure the bundle, there are some required params:

+ `dir.root` specifies the root directory op your app
+ `dir.config` specifies where your configurations files are located. This is necessary for the main `routing.yml` file.
+ `dir.cache` specifies where you will store you cache. The DIC will create a cache file there.

and some optional params:

+ `api.security.enabled` (boolean) to enable or disable the firewall
+ `api.prefix` specifies the prefix of the firewall that will secure the apis
+ `api.name` specifies the name for the api documentation
+ `api.extensions` (array) can be used to pass DIC extensions to the compiler

You can configure it in the normal silex way or you can use the [ConfigServiceProvider](https://github.com/igorw/ConfigServiceProvider) for a yaml implementation. An example of a config.yml below.
```YAML
dir.root: %kernel.root_dir%
dir.config: %kernel.app_dir%/config
dir.cache: %kernel.app_dir%/cache

api.security.enabled: true
api.prefix: /api
api.name: 'Authentication API documentation'
api.extensions:
    - \Foo\Bar\DependencyInjection\FooBarExtension

security.role_hierarchy:
    ROLE_USER: []
    ROLE_ADMIN: [ROLE_USER]
```

## ApiDoc
This system is provided with a ported version of the Nelmio ApiDoc system. This will make it easy to document api calls.
To access it, you need to implement the following into the main routing.yml file:
```yaml
STAB:
    resource: ../../vendor/devolicious/silex-turbo-api-bundle/Devolicious/SilexTurboApiBundle/Resources/config/routing.yml
    prefix: /api/doc
    requirements:
        _method: GET
```

To write documentation, you have to make use of annotations. This is an example of such an annotation you can put above the method you want to document:
```php
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Long description of your method
 *
 *
 * @ApiDoc(
 *  description="small description",
 *  statusCodes={
 *         200="Returned when successful",
 *         400="Returned when the data provided is invalid",
 *         500="Returned when something went wrong, please contact our technical staff"
 *        },
 *  input={
 *          {"name"="Foo", "dataType"="string", "required"=true},
 *        },
 *  output={
 *          {"name"="Bar", "dataType"="json"}
 *        }
 * )
 *
 */
public function getSomething($x)
{
    #code
}
```

In the first part, where you can type free comment, you can use `markdown` markup.
For more info on the [NelmioApiDoc](https://github.com/nelmio/NelmioApiDocBundle) you should checkout the original.

## Workflow

### Dependency Injection
In this little system you can work with the **Symfony2 DIC** component. For more information on [sf2 service container](http://symfony.com/doc/current/book/service_container.html)

### Doctrine
The system is also loaded with doctrine. For more information on [Doctrine2](http://www.doctrine-project.org/)

### Routing
For routing we use the routing component from symfony2. You can configure routes in `app/config/routing.yml`. You can also extend this file to specific bundle routing files.

### Security for HTTP
The firewall is setup to block everything behind `/api` (standard if not configured) except for `/api/doc*`. This means that you always must provide the system with a user and an hashkey.
For routing you best use `/api/{username}/{key}/rest/of/routing` style to provide these necessary parameters.
To create a hash key, you can use the container to fetch the instance of the `ApiEncoder` like this:
```php
$hash = $container->get('stab.security.encoder')->hash($username, $apikey);
```
or you can call a new instance like this:
```php
use Devolicious\SilexTurboApiBundle\Security\Encoder\ApiEncoder;
/*
 some code ...
 */

$encoder = new ApiEncoder();
$hash = $encoder->hash($username, $apikey);
```

#### How to secure a controller method
Via the `Secure`annotation you can do the following:
```php
use Devolicious\SilexTurboApiBundle\Annotation\Secure;

/**
 * @Secure(roles="ROLE_USER")
 */
public function secureThisMethod()
{
    #some code
}
```
This will allow you to secure specific methods to specific priveleges. You will need to define the role hierarchy like you would with the normal [Silex Security](http://silex.sensiolabs.org/doc/providers/security.html#defining-a-role-hierarchy) flow. If your user from the db has the correct priveleges, access will be granted.