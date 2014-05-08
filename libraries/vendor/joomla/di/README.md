# The DI Package [![Build Status](https://travis-ci.org/joomla-framework/di.png?branch=master)](https://travis-ci.org/joomla-framework/di)

The Dependency Injection package for Joomla provides a simple IoC Container for your application.
Dependency Injection allows you the developer to control the construction and lifecycle of your objects,
rather than leaving that control to the classes themselves. Instead of hard coding a class's dependencies
within the class `__construct()` method, you instead provide to a class the dependencies it requires as
arguments to its constructor. This helps to decrease hard dependencies and to create loosely coupled code.

Read more about [why you should be using dependency injection](docs/why-dependency-injection.md).

An Inversion of Control (IoC) Container helps you to manage these dependencies in a controlled fashion.

## Using the Container

### Creating a Container

Creating a container usually happens very early in the application lifecycle. For a Joomla MVC app, this
typically happens in the application's `doExecute` method. This allows your application access to the DI
Container, which you can then use within the app class to build your controllers and their dependencies.

```php
namespace My\App;

use Joomla\DI\Container;
use Joomla\Application\AbstractWebApplication;

class WebApp extends AbstractWebApplication
{
    protected $container;

    // ...snip

    protected function doExecute()
    {
        $this->container = new Container;

        // ...snip
    }
}
```

Another feature of the container is the ability to create a child container with a different resolution
scope. This allows you to easily override an interface binding for a specific controller, without
destroying the resolution scope for the rest of the classes using the container. A child container will
search recursively through it's parent containers to resolve all the required dependencies.

```php
use Joomla\DI\Container;

$container->set('Some\Interface\I\NeedInterface', new My\App\InterfaceImplementation);
// Application executes... Come to a class that needs a different implementation.
$child = $container->createChild();
$child->set('Some\Interface\I\NeedInterface', new My\Other\InterfaceImplementation);
```

### Setting an Item

Setting an item within the container is very straightforward. You pass the `set` method a string `$key`
and a `$value`, which can be pretty much anything. If the `$value` is an anonymous function or a `Closure`,
or a callable value,
that value will be set as the resolving callback for the `$key`. If it is anything else (an instantiated
object, array, integer, serialized controller, etc) it will be wrapped in a closure and that closure will
be set as the resolving callback for the `$key`.

> If the `$value` you are setting is a closure or a callable, it will receive a single function argument,
> the calling container. This allows access to the container within your resolving callback.

```php
// Assume a created $container
$container->set('foo', 'bar');

$container->set('something', new Something);

$container->set('callMe', array($this, 'callMe');
// etc
```

In the case of a callable, the called method must take a `Container` object as its first and only argument.

When setting items in the container, you are allowed to specify whether the item is supposed to be a
shared or protected item. A shared item means that when you get an item from the container, the resolving
callback will be fired once, and the value will be stored and used on every subsequent request for that
item. The other option, protected, is a special status that you can use to prevent others from overwriting
the item down the line. A good example for this would be a global config that you don't want to be
overwritten. The third option is that you can both share AND protect an item. A good use case for this would
be a database connection that you only want one of, and you don't want it to be overwritten.

```php
// Assume a created $container
$container->share(
    'foo',
    function ()
    {
        // some expensive $stuff;

        return $stuff;
    }
);

$container->protect(
    'bar',
    function (Container $c)
    {
        // Don't overwrite my db connection.
        $config = $c->get('config');

        $databaseConfig = (array) $config->get('database');

        return new DatabaseDriver($databaseConfig);
    }
);
```

> Both the `protect` and `share` methods take an optional third parameter. If set to `true`, it will
> tell the container to both protect _and_ share the item. (Or share _and_ protect, depending on
> the origin method you call. Essentially it's the same thing.)

The most powerful feature of setting an item in the container is the ability to bind an implementation
to an interface. This is useful when using the container to build your app objects. You can typehint
against an interface, and when the object gets built, the container will pass your implementation.

@TODO
- Interface binding usage example

### Item Aliases

Any item set in the container can be aliased. This allows you to create an object that is a named
dependency for object resolution, but also have a "shortcut" access to the item from the container.

```php
// Assume a created $container
$container->set(
    'Really\Long\ConfigClassName',
    function ()
    {
        // ...snip
    }
);

$container->alias('config', 'Really\Long\ConfigClassName');

$container->get('config'); // Returns the value set on the aliased key.
```

### Getting an Item

At its most basic level, the DI Container is a registry that holds keys and values. When you set
an item on the container, you can retrieve it by passing the same `$key` to the `get` method that
you did when you set the method in the container.

> If you've aliased a set item, you can also retrieve it using the alias key.

```php
// Assume a created $container
$container->set('foo', 'bar');

$foo = $container->get('foo');
```

Normally, the value you'll be passing will be a closure. When you fetch the item from the container,
the closure is executed, and the result is returned.

```php
// Assume a created $container
$container->set(
    'foo',
    function ()
    {
        // Create an instance of \Joomla\Github\Github;

        return $github;
    }
);

$github = $container->get('foo');

var_dump($github instanceof \Joomla\Github\Github); // prints bool(true)
```

If you get the item again, the closure is executed again and the result is returned.

```php
// Picking up from the previous codeblock
$github2 = $container->get('foo');

print($github2 === $github); // prints bool(false)
```

However, if you specify that the object as shared when setting it in the container, the closure will
only be executed once (the first time it is requested), the value will be stored and then returned
on every subsequent request.
```php
// Assume a created $container
$container->share(
    'twitter',
    function ()
    {
        // Create an instance of \Joomla\Twitter\Twitter;

        return $twitter;
    }
);

$twitter  = $container->get('twitter');
$twitter2 = $container->get('twitter');

var_dump($twitter === $twitter2); // prints bool(true)
```

If you've specified an item as shared, but you really need a new instance of it for some reason, you
can force the creation of a new instance by passing true as the second argument, or using the `getNewInstance`
convenience method.

> When you force create a new instance on a shared object, that new instance replaces the instance
> that is stored in the container and will be used on subsequent requests.

```php
// Picking up from the previous codeblock
$twitter3 = $container->getNewInstance('twitter');

var_dump($twitter === $twitter3); // prints bool(false)

$twitter4 = $container->get('twitter');
var_dump($twitter3 === $twitter4); // prints bool(true)
```

> If you've created a child container, you can use the `get` and `getNewInstance` methods on it to
> fetch items from the parent container that have not yet been overwritten in the child container.


### Instantiate an object from the Container

The most useful function of the container is it's ability to build complete objects, instantiating
any needed dependencies along the way. When you use the container in this way, it looks at a classes
constructor declared dependencies and then automatically passes them into the object.

> Classes will only receive dependencies that have been properly typehinted or given a default value.

Since the container allows you to bind an implementation to an interface, this gives you great flexibility
to build your classes within the container. If your model class requires a user repository, you can typehint
against a `UserRepositoryInterface` and then bind an implementation to that interface to be passed into
the model when it's created.

```php
class User implements UserRepositoryInterface
{
    // ...snip
}

class UserProfile
{
    protected $user;

    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }
}

// Assume a created $container
$container->set(
    'UserRepositoryInterface',
    function ()
    {
        retur new User;
    }
);

$userProfile = $container->buildObject('UserProfile');

// Use reflection to get the $user property from $userProfile
var_dump($user instanceof User); // prints bool(true)
var_dump($user instanceof UserRepositoryInterface); // prints bool(true)
```

When you build an object, the information required to actually build it (dependencies, etc) are
stored in a callable and set in the container with the class name as the key. You can then fetch
the item from the container by name later on. Alias support applies here as well.

You can also specify to build a shared object by using the function `buildSharedObject($key)`. This
works exactly as you would expect. The information required to build it is discovered, stored in a
callable, then the callable is executed and the result returned. The result is stored as an instance
within the container and is returned on subsequent requests.


### Extending an Item

The Container also allows you to extend items. Extending an item can be thought of as a way to
implement the decorator pattern, although it's not really in the strict sense. When you extend an
item, you must pass the key for the item you want to extend, and then a closure as the second
argument. The closure will receive 2 arguments. The first is result of the callable for the given key,
and the second will be the container itself. When extending an item, the new extended version overwrites
the existing item in the container. If you try to extend an item that does not exist, an `\InvalidArgumentException`
will be thrown.

> When extending an item, normal rules apply. A protected object cannot be overwritten, so you also can not extend them.

```php
// Assume a created $container
$container->set('foo', 'bar');

var_dump($container->get('foo')); // prints string(3) "bar"

$container->extend(
    'foo',
    function ($originalResult, Container $c)
    {
        return $originalResult .= 'baz';
    }
);

var_dump($container->get('foo')); // prints string(6) "barbaz"
```


### Service Providers

Another strong feature of the Container is the ability register a _service provider_ to the container.
Service providers are useful in that they are a simple way to encapsulate setup logic for your objects.
In order to use create a service provider, you must implement the `Joomla\DI\ServiceProviderInterface`.
The `ServiceProviderInterface` tells the container that your object has a `register` method that takes
the container as it's only argument.

> Registering service providers is typically done very early in the application lifecycle. Usually
> right after the container is created.

```php
// Assume a created $container
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Database\DatabaseDriver;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share(
            'Joomla\Database\DatabaseDriver',
            function () use ($container)
            {
                $databaseConfig = (array) $container->get('config')->get('database');

                return new DatabaseDriver($databaseConfig);
            },
            true
        );

        $container->alias('db', 'Joomla\Database\DatabaseDriver');
    }
}

$container->registerServiceProvider(new DatabaseServiceProvider);
```

Here is an alternative using a callable.

```php
// Assume a created $container
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

class CallableServiceProvider implements ServiceProviderInterface
{
    public function getCallable(Container $c)
    {
        return 'something';
    }

    public function register(Container $container)
    {
        $container->set('callable', array($this, 'getCallable');
    }
}

$container->registerServiceProvider(new CallableServiceProvider);
```

The advantage here is that it is easier to write unit tests for the callable method (closures can be awkward to isolate
and test).

### Container Aware Objects

You are able to make objects _ContainerAware_ by implementing the `Joomla\DI\ContainerAwareInterface` within your
class. This can be useful when used within the construction level of your application. The construction
level is considered to be anything that is responsible for the creation of other objects. When using
the MVC pattern as recommended by Joomla, this can be at the application or controller level. Controllers
in Joomla are responsible for creating Models and Views, and linking them together. In this case, it would
be reasonable for the controllers to have access to the container in order to build these objects.

> __NOTE:__ The business layer of your app (eg: Models) should _never_ be container aware. Doing so will
> make your code harder to test, and is a far cry from best practices.

### Container Aware Trait

Since PHP 5.4 traits are [available](http://www.php.net/traits), so you can use `ContainerAwareTrait`.

Usage:

```php
use Joomla\DI\ContainerAwareInterface,
	Joomla\DI\ContainerAwareTrait,
	Joomla\Controller\AbstractController;

class MyConroller extends AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

	public function execute()
	{
		$container = $this->getContainer();
	}
}
```

## Installation via Composer

Add `"joomla/di": "~1.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/di": "~1.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/di "~1.0"
```
