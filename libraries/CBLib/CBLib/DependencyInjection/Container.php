<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/28/13 2:01 PM $
* @package CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\DependencyInjection;

use CBLib\DependencyInjection\Exception\BindingResolutionException;
use CBLib\Registry\HierarchyInterface;
use CBLib\Observer\ObserverInterface;
use CBLib\Observer\ObserverMapper;
use CBLib\Observer\ObserverUpdater;
use Closure;
use ReflectionClass;
use ReflectionParameter;

defined('CBLIB') or die();

/**
 * Class Container
 *
 * Partly inspired with thanks by http://laravel.com/docs/ioc, Symfony, Joomla, Phalcon,
 * But extending it with Hierarchical parents and with ObservableInterface
 * and with ServiceProviderInterface and with dynamic getABSTRACT() methods
 * and with defaultContainer and with mainly non-static use.
 * Updated 2014-05-06 with latest fixes as of 2014-04-21 from (except for trailing \ that we do not need):
 * https://github.com/laravel/framework/blob/master/src/Illuminate/Container/Container.php
 */
class Container implements ContainerInterface {
	/**
	 * The container's bindings.
	 *
	 * @var array
	 */
	protected $bindings = array();

	/**
	 * The container's shared instances.
	 *
	 * @var array
	 */
	protected $instances = array();

	/**
	 * The registered type aliases.
	 *
	 * @var array
	 */
	protected $aliases = array();

	/**
	 * @var ObserverUpdater
	 */
	protected $observers;

	/**
	 * All of the registered rebound callbacks.
	 *
	 * @var array
	 */
	protected $reboundCallbacks = array();

	/**
	 * @var Container
	 */
	protected $parent = null;

	/**
	 * Constructor for the DI Container
	 *
	 * @param  ContainerInterface  $parent  [optional] Parent for hierarchical containers.
	 */
	public function __construct( ContainerInterface $parent = null )
	{
		$this->parent		=	$parent;

		// Create observer updater and attaches all observers interested by $this class:
		$this->observers	=	new ObserverUpdater($this);
		ObserverMapper::attachAllObservers($this);
	}

	/**
	 * Alias of bind() and instance():
	 * Register a binding or an instance (object) with the container.
	 *
	 * @param  string|array               $abstract   Abstract type (string) or array( $abstract, $alias )
	 * @param  Closure|string|object|null $concrete   [optional] Concrete class, Closure function(ContainerInterface $di, array $parameters) (with $parameters from the get() function) to get an instance or null (for class $abstract)
	 * @param  bool                       $shared     Shared instance or create new instance each time
	 * @param  bool                       $overwrite  Overwrite existing binding if exist
	 * @return Container                              For chaining
	 *
	 * @throws \InvalidArgumentException              If an object $concrete is given but $shared = false
	 */
	public function set( $abstract, $concrete = null, $shared = false, $overwrite = true )
	{
		if ( $overwrite || ! $this->bound( $abstract ) )
		{
			if ( is_object( $concrete ) && ! $concrete instanceof Closure )
			{
				if ( $shared )
				{
					$this->instance( $abstract, $concrete );
				}
				else
				{
					throw new \InvalidArgumentException('Called CBLib Container::set() with a concrete object but $shared=false.');
				}
			}
			else
			{
				$this->bind( $abstract, $concrete, $shared );
			}
		}
		// For chaining:
		return $this;
	}

	/**
	 * Alias of make() as that name is better suited for existing instances.
	 * Resolve the given type from the container.
	 *
	 * @param  string  $abstract
	 * @param  array   $parameters
	 * @return object
	 */
	public function get( $abstract, $parameters = array() )
	{
		return $this->make($abstract, $parameters);
	}

	/**
	 * Determine if a given string is resolvable.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function has( $abstract )
	{
		return $this->bound( $abstract )
			|| ( $this->isAlias( $abstract ) && $this->bound( $this->getAlias( $abstract ) ) );
	}

	/**
	 * Create a child Container with a new property scope that
	 * that has the ability to access the parent scope when resolving.
	 *
	 * @return self
	 */
	public function createChild( )
	{
		return new static( $this );
	}

	/**
	 * Sets the parent of $this
	 *
	 * @param   HierarchyInterface  $parent  The parent of this object
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function setParent( HierarchyInterface $parent )
	{
		if ( ! ( $parent instanceof ContainerInterface ) )
		{
			throw new \InvalidArgumentException( sprintf( 'Invalid %s::%s parent', __CLASS__, __FUNCTION__ ) );
		}
		$this->parent	=	$parent;
	}

	/**
	 * Gets the parent of $this
	 *
	 * @return  ContainerInterface  The parent of this object
	 */
	public function getParent( )
	{
		return $this->parent;
	}

	/**
	 * ServiceProviderInterface pattern function:
	 * Registers a service provider to the container.
	 * The service provider will call-back $this->bind() to bind its classes/objects
	 *
	 * @param   ServiceProviderInterface  $provider  The service provider to register
	 *
	 * @return  Container  This object for chaining.
	 */
	public function registerServiceProvider( ServiceProviderInterface $provider )
	{
		$provider->register( $this );

		return $this;
	}

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function bound($abstract)
	{
		return isset($this[$abstract]) || isset($this->instances[$abstract])
			|| ( isset( $this->parent ) && $this->parent->bound( $abstract ) );
	}

	/**
	 * Determine if a given string is an alias.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function isAlias($name)
	{
		return isset($this->aliases[$name])
			|| ( isset( $this->parent ) && $this->parent->isAlias( $name ) );
	}

	/**
	 * Register a binding with the container.
	 * (not needed to be public anymore with the set() method that uses this)
	 *
	 * @param  string|array         $abstract  Abstract type (string) or array( $abstract, $alias )
	 * @param  Closure|string|null  $concrete  [optional] Concrete class, Closure to get an instance or null (for class $abstract)
	 * @param  bool                 $shared    Shared instance or create new instance each time
	 * @return void
	 */
	protected function bind($abstract, $concrete = null, $shared = false)
	{
		// If the given types are actually an array, we will assume an alias is being
		// defined and will grab this "real" abstract class name and register this
		// alias with the container so that it can be used as a shortcut for it.
		if (is_array($abstract))
		{
			list($abstract, $alias) = $this->extractAlias($abstract);

			$this->alias($abstract, $alias);
		}

		// If no concrete type was given, we will simply set the concrete type to the
		// abstract type. This will allow concrete type to be registered as shared
		// without being forced to state their classes in both of the parameter.
		$this->dropStaleInstances($abstract);

		if (is_null($concrete))
		{
			$concrete = $abstract;
		}

		// If the factory is not a Closure, it means it is just a class name which is
		// is bound into this container to the abstract type and we will just wrap
		// it up inside a Closure to make things more convenient when extending.
		if ( ! $this->isCallable( $concrete ) )
		{
			$concrete = $this->getClosure($abstract, $concrete);
		}

		$bound = $this->bound($abstract);

		$this->bindings[$abstract] = compact('concrete', 'shared');

		// If the abstract type was already bound in this container, we will fire the
		// rebound listener so that any objects which have already gotten resolved
		// can have their copy of the object updated via the listener callbacks.
		if ($bound)
		{
			$this->rebound($abstract);
		}
	}

	/**
	 * Get the Closure to be used when building a type.
	 *
	 * @param  string  $abstract
	 * @param  string  $concrete
	 * @return \Closure
	 */
	protected function getClosure($abstract, $concrete)
	{
		return function($c, $parameters = array()) use ($abstract, $concrete)
		{
			$method = ($abstract == $concrete) ? 'build' : 'make';

			return $c->$method($concrete, $parameters);
		};
	}

	/**
	 * Utility function wrapping a Closure into a singleton closure such that it is shared.
	 *
	 * @param  Closure  $closure
	 * @return Closure
	 */
	public function share(Closure $closure)
	{
		return function($container) use ($closure)
		{
			// We'll simply declare a static variable within the Closures and if it has
			// not been set we will execute the given Closures to resolve this value
			// and return it back to these consumers of the method as an instance.
			static $object;

			if (is_null($object))
			{
				$object = $closure($container);
			}

			return $object;
		};
	}

	/**
	 * Bind a shared Closure into the container.
	 *
	 * @param  string              $abstract  Abstract type (string) or array( $abstract, $alias )
	 * @param  \Closure            $closure   Closure returning shared instance
	 * @return ContainerInterface             For chaining
	 */
	public function bindShared($abstract, Closure $closure)
	{
		$this->bind($abstract, $this->share($closure), true);

		return $this;
	}

	/**
	 * Extend an abstract type in the container, wrapping it with the new $closure.
	 * This implements the Decorator pattern for service providers
	 *
	 * @param  string   $abstract
	 * @param  Closure  $closure
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function extend($abstract, Closure $closure)
	{
		if ( ! $this->hasBinding( $abstract ) )
		{
			throw new \InvalidArgumentException("Type {$abstract} is not bound.");
		}

		if (isset($this->instances[$abstract]))
		{
			$this->instances[$abstract] = $closure($this->instances[$abstract], $this);

			$this->rebound($abstract);
		}
		else
		{
			$extender = $this->getExtender($abstract, $closure);

			$this->bind($abstract, $extender, $this->isShared($abstract));
		}
	}

	/**
	 * Get an extender Closure for resolving a type.
	 *
	 * @param  string  $abstract
	 * @param  \Closure  $closure
	 * @return \Closure
	 */
	protected function getExtender($abstract, Closure $closure)
	{
		// To "extend" a binding, we will grab the old "resolver" Closure and pass it
		// into a new one. The old resolver will be called first and the result is
		// handed off to the "new" resolver, along with this container instance.
		$resolver = $this->getConcrete( $abstract );

		return function($container) use ($resolver, $closure)
		{
			return $closure($resolver($container), $container);
		};
	}

	/**
	 * Register an existing instance as shared in the container.
	 * (not needed to be public anymore with the set() method that uses this)
	 *
	 * @param  string|array  $abstract
	 * @param  object        $instance
	 * @return void
	 */
	protected function instance($abstract, $instance)
	{
		// First, we will extract the alias from the abstract if it is an array so we
		// are using the correct name when binding the type. If we get an alias it
		// will be registered with the container so we can resolve it out later.
		if (is_array($abstract))
		{
			list($abstract, $alias) = $this->extractAlias($abstract);

			$this->alias($abstract, $alias);
		}

		unset($this->aliases[$abstract]);

		// We'll check to determine if this type has been bound before, and if it has
		// we will fire the rebound callbacks registered with the container and it
		// can be updated with consuming classes that have gotten resolved here.
		$bound = $this->bound($abstract);

		$this->instances[$abstract] = $instance;

		if ($bound)
		{
			$this->rebound($abstract);
		}
	}

	/**
	 * Alias a type to a shorter name.
	 *
	 * @param  string              $abstract
	 * @param  string              $alias
	 * @return ContainerInterface             For chaining
	 */
	public function alias($abstract, $alias)
	{
		$this->aliases[$alias] = $abstract;

		return $this;
	}

	/**
	 * Extract the type and alias from a given definition.
	 *
	 * @param  array  $definition
	 * @return array
	 */
	protected function extractAlias(array $definition)
	{
		return array(key($definition), current($definition));
	}

	/**
	 * Adds an observer to this ObservableInterface instance.
	 * This method will be called automatically fron the constructor of ObserverInterface
	 * which will be instanciated by ObserverMapper.
	 * The implementation of this function can use ObserverUpdater (see above class description)
	 *
	 * @param   ObserverInterface    $observer  The observer to attach to $this observable subject
	 * @return  void
	 */
	public function attachObserver( ObserverInterface $observer ) {
		$this->observers->attachObserver($observer);
	}

	/**
	 * Fire the "rebound" callbacks for the given abstract type.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	protected function rebound($abstract)
	{
		$instance = $this->make($abstract);

		$this->observers->update( 'onContainerRebound', array( $abstract, $instance ) );
	}

	/**
	 * Resolve the given type from the container.
	 *
	 * @param  string  $abstract
	 * @param  array   $parameters
	 * @return object
	 */
	public function make($abstract, $parameters = array())
	{
		$abstract = $this->getAlias($abstract);

		// If an instance of the type is currently being managed as a singleton we'll
		// just return an existing instance instead of instantiating new instances
		// so the developer can keep using the same objects instance every time.
		$instance = $this->getInstance( $abstract );
		if ( $instance )
		{
			return $instance;
		}

		$concrete = $this->getConcrete($abstract);

		// We're ready to instantiate an instance of the concrete type registered for
		// the binding. This will instantiate the types, as well as resolve any of
		// its "nested" dependencies recursively until all have gotten resolved.
		if ($this->isBuildable($concrete, $abstract))
		{
			$object = $this->build($concrete, $parameters);
		}
		else
		{
			$object = $this->make($concrete, $parameters);
		}

		// If the requested type is registered as a singleton we'll want to cache off
		// the instances in "memory" so we can return it later without creating an
		// entirely new instance of an object on each subsequent request for it.
		if ($this->isShared($abstract))
		{
			$this->instances[$abstract] = $object;
		}

		$this->fireResolvingCallbacks($abstract, $object);

		return $object;
	}

	/**
	 * Checks if $this or any parent have an instance of $abstract class, and returns
	 * it if found, otherwise returns null.
	 *
	 * @param  string  $abstract
	 * @return object|null
	 */
	protected function getInstance( $abstract )
	{
		// If an instance of the type is currently being managed as a singleton return it:
		if ( isset( $this->instances[$abstract] ) )
		{
			return $this->instances[$abstract];
		}
		// If not, try all parents:
		if ( $this->parent ) {
			return $this->parent->getInstance( $abstract );
		}
		// Last parent will return null if none found it:
		return null;
	}

	/**
	 * Checks if a given abstract has a binding (which is not through instance())
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	protected function hasBinding( $abstract )
	{
		if ( isset( $this->bindings[$abstract] ) )
		{
			return true;
		}

		// If we don't have a concrete type for a given abstract, but have a parent,
		// task the parent to get the concrete binding:
		if ( $this->parent )
		{
			return $this->parent->hasBinding( $abstract );
		}

		return false;
	}

	/**
	 * Get the concrete type for a given abstract.
	 *
	 * @param  string           $abstract
	 * @return string|Closure   $concrete
	 */
	protected function getConcrete($abstract)
	{
		if ( isset($this->bindings[$abstract]))
		{
			return $this->bindings[$abstract]['concrete'];
		}

		// If we don't have a concrete type for a given abstract, but have a parent,
		// task the parent to get the concrete type:
		if ( $this->parent )
		{
			return $this->parent->getConcrete( $abstract );
		}

		// If we don't have a registered resolver or concrete for the type, we'll just
		// assume each type is a concrete name and will attempt to resolve it as is
		// since the container should be able to resolve concretes automatically.
		return $abstract;
	}

	/**
	 * Instantiate a concrete instance of the given type.
	 *
	 * Do NOT use from outside this class: This function is public only for working around a bug in PHP 5.3 hit in function getClosure() !
	 *
	 * @param  string  $concrete
	 * @param  array   $parameters
	 * @return object
	 *
	 * @throws BindingResolutionException
	 */
	public function build($concrete, $parameters = array())
	{
		// If the concrete type is actually a Closure, we will just execute it and
		// hand back the results of the functions, which allows functions to be
		// used as resolvers for more fine-tuned resolution of these objects.
		if ( $this->isCallable( $concrete ) )
		{
			return $concrete($this, $parameters);
		}

		$reflector = new ReflectionClass($concrete);

		// If the type is not instantiable, the developer is attempting to resolve
		// an abstract type such as an Interface of Abstract Class and there is
		// no binding registered for the abstractions so we need to bail out.
		if ( ! $reflector->isInstantiable())
		{
			$message = "Target [$concrete] is not instantiable.";

			throw new BindingResolutionException($message);
		}

		$constructor = $reflector->getConstructor();

		// If there are no constructors, that means there are no dependencies then
		// we can just resolve the instances of the objects right away, without
		// resolving any other types or dependencies out of these containers.
		if (is_null($constructor))
		{
			return new $concrete;
		}

		$dependencies = $constructor->getParameters();

		// Once we have all the constructor's parameters we can create each of the
		// dependency instances and then use the reflection instances to make a
		// new instance of this class, injecting the created dependencies in.
		$parameters = $this->keyParametersByArgument(
			$dependencies, $parameters
		);

		$instances = $this->getDependencies(
			$dependencies, $parameters
		);

		return $reflector->newInstanceArgs($instances);
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array  $parameters
	 * @param  array  $primitives
	 * @return array
	 */
	protected function getDependencies($parameters, array $primitives = array())
	{
		$dependencies = array();

		foreach ($parameters as $parameter)
		{
			/** @var ReflectionParameter $parameter */
			$dependency = $parameter->getClass();

			// If the class is null, it means the dependency is a string or some other
			// primitive type which we can not resolve since it is not a class and
			// we will just bomb out with an error since we have no-where to go.
			if (array_key_exists($parameter->name, $primitives))
			{
				$dependencies[] = $primitives[$parameter->name];
			}
			elseif (is_null($dependency))
			{
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{
				$dependencies[] = $this->resolveClass($parameter);
			}
		}

		return (array) $dependencies;
	}

	/**
	 * Resolve a non-class hinted dependency.
	 *
	 * @param  ReflectionParameter  $parameter
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveNonClass(ReflectionParameter $parameter)
	{
		if ($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		else
		{
			$message = "Unresolvable dependency resolving [$parameter].";

			throw new BindingResolutionException($message);
		}
	}

	/**
	 * Resolve a class based dependency from the container.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveClass(ReflectionParameter $parameter)
	{
		try
		{
			return $this->make($parameter->getClass()->name);
		}

		// If we can not resolve the class instance, we will check to see if the value
		// is optional, and if it is we will return the optional parameter value as
		// the value of the dependency, similarly to how we do this with scalars.
		catch (BindingResolutionException $e)
		{
			if ($parameter->isOptional())
			{
				return $parameter->getDefaultValue();
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * If extra parameters are passed by numeric ID, rekey them by argument name.
	 *
	 * @param  array  $dependencies
	 * @param  array  $parameters
	 * @param  array
	 * @return array
	 */
	protected function keyParametersByArgument(array $dependencies, array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			if (is_numeric($key))
			{
				unset($parameters[$key]);

				$parameters[$dependencies[$key]->name] = $value;
			}
		}

		return $parameters;
	}

	/**
	 * Fire all of the resolving callbacks.
	 *
	 * @param  string  $abstract
	 * @param  object  $object
	 * @return void
	 */
	protected function fireResolvingCallbacks($abstract, $object)
	{
		$this->observers->update( 'onContainerResolving', array( $abstract, $object ) );
	}

	/**
	 * Determine if a given type is shared.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function isShared($abstract)
	{
		if (isset($this->bindings[$abstract]['shared']))
		{
			$shared = $this->bindings[$abstract]['shared'];
		}
		else
		{
			$shared = false;
		}

		return isset($this->instances[$abstract]) || $shared === true;
	}

	/**
	 * Determine if the given concrete is buildable.
	 *
	 * @param  string|Closure|callable  $concrete
	 * @param  string                   $abstract
	 * @return bool
	 */
	protected function isBuildable($concrete, $abstract)
	{
		return $concrete === $abstract || $this->isCallable( $concrete );
	}

	/**
	 * Checks if $concrete is a Closure or a callable
	 * (but not a string in case the $concrete matches a function by coincidence)
	 *
	 * @param  Closure  $concrete
	 * @return bool
	 */
	protected function isCallable( $concrete )
	{
		return ( $concrete instanceof Closure || ( is_callable( $concrete ) && ! is_string( $concrete ) ) );
	}

	/**
	 * Get the alias for an abstract if available.
	 *
	 * @param  string  $abstract
	 * @return string
	 */
	protected function getAlias($abstract)
	{
		return isset($this->aliases[$abstract]) ?
			$this->aliases[$abstract]
			:
			( isset( $this->parent ) ?
				$this->parent->getAlias($abstract)
				:
				$abstract
			);
	}

	/**
	 * Get the container's bindings.
	 *
	 * @return array
	 */
	public function getBindings()
	{
		return $this->bindings;
	}

	/**
	 * Drop all of the stale instances and aliases.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	protected function dropStaleInstances($abstract)
	{
		unset($this->instances[$abstract]);

		unset($this->aliases[$abstract]);
	}

	/**
	 * Remove a resolved instance from the instance cache.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	public function forgetInstance($abstract)
	{
		unset($this->instances[$abstract]);
	}

	/**
	 * Clear all of the instances from the container.
	 *
	 * @return void
	 */
	public function forgetInstances()
	{
		$this->instances = array();
	}

	/**
	 * ArrayAccess Functions
	 */

	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->bindings[$key]);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return object
	 */
	public function offsetGet($key)
	{
		return $this->make($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  Closure|mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		// If the value is not a Closure, we will make it one. This simply gives
		// more "drop-in" replacement functionality for the Pimple which this
		// container's simplest functions are base modeled and built after.
		if ( ! $this->isCallable( $value ) )
		{
			$value = function() use ($value)
			{
				return $value;
			};
		}

		$this->bind($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->bindings[$key]);

		unset($this->instances[$key]);
	}

}
