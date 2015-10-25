<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 09.06.13 01:12 $
* @package CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Core;

use CBLib\Application\Application;
use CBLib\Application\Config;
use CBLib\Database\Database;
use CBLib\Application\ApplicationContainerInterface;
use CBLib\Entity\User\User;
use CBLib\Input\InputInterface;
use CBLib\Language\TranslationsLogger;
use CBLib\Output\Output;
use CBLib\Date\Date;

define( 'CBLIB', '2.0.11' );	// IMPORTANT: when changing version here also change in the 8 XML installation files and in libraries/CBLib/CB/Legacy/cbInstallerPlugin.php and build.xml

include_once __DIR__ . '/AutoLoader.php';

/**
 * CBLib\CBLib Foundation/Factory Class implementation
 *
 * This class is a singleton (unique) handling CBLib on top of the CMS
 * 
 */
class CBLib
{
	/**
	 * @var Application
	 */
	protected static $application		=	null;

	/**
	 * Initializes Auto-loader
	 *
	 * @return void
	 */
	public static function init( )
	{
		AutoLoader::setup();
	}

	/**
	 * Create CBLib Application if not already created, and returns it for chaining
	 *
	 * @param  string                            $type    [optional] 'Web' or 'Cli'
	 * @param  array|object|InputInterface|null  $input   (array_merge(get, post) or argv if cli)
	 * @param  Config|callable|null              $config  The Config to use (or a closure returning it)
	 * @return Application
	 */
	public static function createApplication( $type = 'Web', $input = null, $config = null )
	{
		if ( ! static::$application )
		{
			// Define $app Containers 'Application' and 'Cms':
			$application			=	Application::createApplication( $type );
			static::$application	=	$application;

			// Define $app Container 'Config':
			$application->set( 'Config',
				function ( ) use ( $config, $application )
				{
					return Config::setMainConfig( $config, $application );
				},
				true, true
			);

			// Define $app Container 'DatabaseDriverInterface':
			$application->set( 'CBLib\Database\DatabaseDriverInterface',
				function ( ApplicationContainerInterface $di ) {
					return Database::createDatabaseDriver( $di->getCms() );
				},
				true
			)
				->alias( 'CBLib\Database\DatabaseDriverInterface', 'Database' );

			// Define $app Container 'Input':
			$application->set(
				'CBLib\Input\InputInterface',
				function ( ApplicationContainerInterface $di ) use ( $type, $input )
				{
					// return static::getMainInput( static::$app, $type, $input );
					return $di->getCms()->getInput( $di, $type, $input );
				},
				true
			)
				->alias( 'CBLib\Input\InputInterface', 'Input' );

			// Define $app Container 'Output':
			/** @noinspection PhpUnusedParameterInspection */
			$application->set(
				'CBLib\Output\OutputInterface',
				function ( ApplicationContainerInterface $di, array $parameters )
				{
					return Output::createNew( 'html', $parameters );				//TODO json+xml
				},
				true
			)
				->alias( 'CBLib\Output\OutputInterface', 'Output' );

			// 'Router' and CBLib\Controller\RouterInterface service providers are defined in specific Cms constructor.

			// Define $app Container 'Session':
			$application->set( 'CBLib\Session\SessionInterface', '\CBLib\Session\Session' )
				->alias( 'CBLib\Session\SessionInterface' , 'Session' );

			// Define $app Container 'SessionState':
			$application->set( 'CBLib\Session\SessionStateInterface', '\CBLib\Session\SessionState' )
				->alias( 'CBLib\Session\SessionStateInterface' , 'SessionState' );

			// Define $app Container 'User':
			$application->set( 'CBLib\Entity\User\User',
				function ( ApplicationContainerInterface $di, array $parameters ) {
					if ( count( $parameters ) === 0 ) {
						throw new \UnexpectedValueException( 'Application::MyUser() called without a parameter' );
					}
					return User::getInstanceForContainerOnly( $parameters[0], $di->getCms(), $di->getConfig() );
				}
			)
				->alias( 'CBLib\Entity\User\User', 'User' );
			$application->set( 'MyUser',
				function ( ApplicationContainerInterface $di, array $parameters ) {
					if ( count( $parameters ) !== 0 ) {
						throw new \UnexpectedValueException( 'Application::User() called with a parameter' );
					}
					return User::getInstanceForContainerOnly( null, $di->getCms(), $di->getConfig() );
				}
			);

			// Define $app Container 'Date':
			$application->set(
				'CBLib\Date\Date',
				function ( ApplicationContainerInterface $di, array $parameters ) {
					return new Date( ( isset( $parameters[0] ) ? $parameters[0] : 'now' ), ( isset( $parameters[1] ) ? $parameters[1] : null ), ( isset( $parameters[2] ) ? $parameters[2] : null ), $di->getConfig() );
				}
			)
				->alias( 'CBLib\Date\Date', 'Date' );

			// Define Language and translations, as well as the translations logger interface:
			$application->set( 'Language', 'CBLib\Language\CBTxt', true );
			$application->set(
				'CBLib\Language\TranslationsLoggerInterface',
				function ( ApplicationContainerInterface $di )
				{
					// Creates the logger:
					$translationsLogger	=	new TranslationsLogger();

					// Registers after-render event to add the translations log at the end of the html body:
					$di->getCms()->registerOnAfterRenderBodyFilter(
						function( $body ) use ( $translationsLogger ) {
							return $translationsLogger->appendToBodyUsedStrings( $body );
						}
					);

					return $translationsLogger;
				},
				true
			);
		}

		return static::$application;
	}

	/**
	 * Executes the request, creating everything needed and routing it through CBLib
	 *
	 * @return Output
	 */
	public static function execute( )
	{
		static::$application->dispatch();
		return static::$application->getOutput();
	}
}
