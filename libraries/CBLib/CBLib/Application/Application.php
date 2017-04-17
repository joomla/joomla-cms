<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10.06.13 16:31 $
* @package CBLib\Core
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace CBLib\Application;

use CBLib\Cms\Cms;
use CBLib\Controller\Dispatcher;
use CBLib\DependencyInjection\Container;
use CBLib\DependencyInjection\Exception\BindingResolutionException;

defined('CBLIB') or die();

/**
 * CBLib\Application Class implementation
 *
 * Represents the instance being executed, and gives access to the Config, the Input, Output, Session, Database and Logger, has the main execute method.
 *
 * This is the class of the (identified) application, already pre-routed, thus must be derived from this class.
 * The right child class is created by the main Dispatcher class using the Router class
 *
 * This class is also the main Container for the Application's Dependency Injection hierarchical Containers.
 *
 * @method static \CBFramework                             CBFramework()     get CB Framework (legacy one)
 * @method static \CBLib\Database\DatabaseDriverInterface  Database()        get CBLib Database
 * @method static \CBLib\Application\Application           Application()     get CBLib Application
 * @method static \CBLib\Application\ApplicationContainerInterface   DI()    get CBLib Application
 * @method static \CBLib\Application\Config                Config()          get CBLib Config
 * @method static \CBLib\Cms\CmsInterface                  Cms()             get CBLib Cms
 * @method static \CBLib\Cms\CmsPermissionsInterface       CmsPermissions()  get CBLib CmsPermissions
 * @method static \CBLib\Controller\RouterInterface        Router()          get CBLib Router
 * @method static \CBLib\Input\Input                       Input()           get CBLib Input
 * @method static \CBLib\Output\Output                     Output()          get CBLib Output
 * @method static \CBLib\Session\Session                   Session()         get CBLib Session
 * @method static \CBLib\Entity\User\User                  User( $idOrConditions = null )  get CBLib User
 * @method static \CBLib\Entity\User\User                  MyUser()          get CBLib User of current user
 * @method static \CBLib\Date\Date                         Date( $date = null, $tz = null, $from = null )  get CBLib Date
 * @method \CBFramework                             getCBFramework()     get CB Framework
 * @method \CBLib\Database\DatabaseDriverInterface  getDatabase()        get CBLib DatabaseDriverInterface
 * @method \CBLib\Application\Application           getApplication()     get CBLib Application
 * @method \CBLib\Application\ApplicationContainerInterface   getDI()    get CBLib Application
 * @method \CBLib\Application\Config                getConfig()          get CBLib Config
 * @method \CBLib\Cms\CmsInterface                  getCms()             get CBLib Cms
 * @method \CBLib\Cms\CmsPermissionsInterface       getCmsPermissions()  get CBLib CmsPermissions
 * @method \CBLib\Controller\RouterInterface        getRouter()          get CBLib Router
 * @method \CBLib\Input\Input                       getInput()           get CBLib Input
 * @method \CBLib\Output\Output                     getOutput()          get CBLib Output
 * @method \CBLib\Session\Session                   getSession()         get CBLib Session
 * @method \CBLib\Entity\User\User                  getUser( $idOrConditions = null )      get CBLib User
 * @method \CBLib\Entity\User\User                  getMyUser()          get CBLib User
 */
abstract class Application extends Container implements ApplicationContainerInterface
{
	/**
	 * @var int
	 */
	protected $startTime;

	/**
	 * @var float
	 */
	protected $startTimeMicroSeconds;

	/**
	 * @var Container
	 */
	protected static $defaultContainer = null;

	/**
	 * Creates the correct type of Application
	 *
	 * @param  string                   $type      'Web' or 'Cli'
	 * @return  Application
	 *
	 * @throws \Exception
	 */
	public static function createApplication( $type = 'Web' )
	{
		if ( $type == 'Web' ) {
			// We use getDI instead of new, as we want at same time to set the default DI:
			$application		=	Web::createDI();
		} elseif ( $type == 'Cli' ) {
			$application		=	Cli::createDI();
		} else {
			throw new \Exception('Unknown Application type', E_USER_ERROR );
		}

		$application->startTimeMicroSeconds	=	isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime( true );
		$application->startTime				=	isset( $_SERVER['REQUEST_TIME'] ) ? $_SERVER['REQUEST_TIME'] : time();

		$application->set( 'CBLib\Application\ApplicationContainerInterface', $application, true )
			->alias( 'CBLib\Application\ApplicationContainerInterface', 'DI' )
			->alias( 'CBLib\Application\ApplicationContainerInterface', 'Application' );

		$application->set( 'CBLib\Cms\CmsInterface', Cms::getGetCmsFunction(), true )
			->alias( 'CBLib\Cms\CmsInterface', 'Cms' );

		return $application;
	}

	/**
	 * Returns start $_SERVER['REQUEST_TIME'] or time() of Application (or the float/microtime())
	 *
	 * @param  boolean    $float  [optional, default false]. True: Float with seconds.microseconds
	 * @return int|float
	 */
	public function getStartTime( $float = false )
	{
		return $float ? $this->startTimeMicroSeconds : $this->startTime;
	}

	/**
	 * Handles a CBLib\inputInterface request and returns the corresponding CBLib\Output\Output in $this Container
	 * using CBLib\Dispatcher (which uses CBLib\RouterInterface and invokes a CBLib\Controller)
	 * Raises an CBLib\Exception if task to do is not known or not authorized.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function dispatch( )
	{
		/** @var Dispatcher $dispatcher */
		$dispatcher		=	$this->get( 'CBLib\Controller\Dispatcher' );
		$dispatcher->dispatch();
	}

	/**
	 * Gets the default DI if $di == null, creates it if needed.
	 *
	 * @return static
	 */
	protected static function createDI()
	{
		self::$defaultContainer		=	new static();

		return self::$defaultContainer;
	}

	/**
	 * Implements methods getABSTRACT( arguments )
	 *
	 * @param  string  $name
	 * @param  array   $arguments
	 * @return object
	 *
	 * @throws BindingResolutionException
	 */
	public function __call( $name, $arguments )
	{
		if ( substr( $name, 0, 3 ) === 'get' )
		{
			$abstract	=	substr( $name, 3 );
			return $this->make( $abstract, $arguments );
		}
		$trace=debug_backtrace( 24 );
		$caller=array_shift($trace);
		throw new BindingResolutionException( get_class( $this ) . ': Undefined method: ' . $name . ' called in ' . var_export( $caller, true ) );
	}

	/**
	 * Implements methods getABSTRACT( arguments )
	 *
	 * @param  string  $name
	 * @param  array   $arguments
	 * @return object
	 *
	 * @throws \BadFunctionCallException
	 */
	public static function __callStatic( $name, array $arguments )
	{
		return self::$defaultContainer->make( $name, $arguments );
	}
}
