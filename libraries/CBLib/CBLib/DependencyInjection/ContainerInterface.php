<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 12/10/13 12:55 AM $
* @package CBLib\DependencyInjection
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
namespace CBLib\DependencyInjection;

use CBLib\Registry\HierarchyInterface;
use CBLib\Observer\ObservableInterface;
use Closure;
use ArrayAccess;

defined('CBLIB') or die();

/**
 * Interface ContainerInterface
 */
interface ContainerInterface extends ArrayAccess, ObservableInterface, HierarchyInterface
{
	/**
	 * Constructor for the DI Container
	 *
	 * @param  ContainerInterface  $parent  [optional] Parent for hierarchical containers.
	 */
	public function __construct( ContainerInterface $parent = null );

	/**
	 * Alias of bind() and instance():
	 * Register a binding or an instance (object) with the container.
	 *
	 * @param  string|array               $abstract   Abstract type (string) or array( $abstract, $alias )
	 * @param  Closure|string|object|null $concrete   [optional] Concrete class, Closure to get an instance or null (for class $abstract)
	 * @param  bool                       $shared     Shared instance or create new instance each time
	 * @param  bool                       $overwrite  Overwrite existing binding if exist
	 * @return ContainerInterface                     For chaining
	 *
	 * @throws \InvalidArgumentException              If an object $concrete is given but $shared = false
	 */
	public function set( $abstract, $concrete = null, $shared = false, $overwrite = true );

	/**
	 * Alias of make() as that name is better suited for existing instances.
	 * Resolve the given type from the container.
	 *
	 * @param  string  $abstract
	 * @param  array   $parameters
	 * @return object
	 */
	public function get( $abstract, $parameters = array() );

	/**
	 * Determine if a given string is resolvable.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function has( $abstract );

	/**
	 * Create a child Container with a new property scope that
	 * that has the ability to access the parent scope when resolving.
	 *
	 * @return self
	 */
	public function createChild( );

	/**
	 * ServiceProviderInterface pattern function:
	 * Registers a service provider to the container.
	 * The service provider will call-back $this->bind() to bind its classes/objects
	 *
	 * @param   ServiceProviderInterface  $provider  The service provider to register
	 *
	 * @return  ContainerInterface  This object for chaining.
	 */
	public function registerServiceProvider( ServiceProviderInterface $provider );

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function bound($abstract);

	/**
	 * Determine if a given string is an alias.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function isAlias($name);

	/**
	 * Bind a shared Closure into the container.
	 *
	 * @param  string              $abstract  Abstract type (string) or array( $abstract, $alias )
	 * @param  \Closure            $closure   Closure returning shared instance
	 * @return ContainerInterface             For chaining
	 */
	public function bindShared($abstract, Closure $closure);

	/**
	 * Utility function wrapping a Closure into a singleton closure such that it is shared.
	 *
	 * @param  Closure  $closure
	 * @return Closure
	 */
	public function share(Closure $closure);

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
	public function extend($abstract, Closure $closure);

	/**
	 * Alias a type to a shorter name.
	 *
	 * @param  string              $abstract
	 * @param  string              $alias
	 * @return ContainerInterface             For chaining
	 */
	public function alias($abstract, $alias);

	/**
	 * Resolve the given type from the container.
	 *
	 * @param  string  $abstract
	 * @param  array   $parameters
	 * @return object
	 */
	public function make($abstract, $parameters = array());

	/**
	 * Determine if a given type is shared.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function isShared($abstract);

	/**
	 * Get the container's bindings.
	 *
	 * @return array
	 */
	public function getBindings();

	/**
	 * Remove a resolved instance from the instance cache.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	public function forgetInstance($abstract);

	/**
	 * Clear all of the instances from the container.
	 *
	 * @return void
	 */
	public function forgetInstances();
}
