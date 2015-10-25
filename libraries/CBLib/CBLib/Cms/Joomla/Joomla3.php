<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 08.06.13 22:32 $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla;

use CBLib\Application\Application;
use CBLib\Cms\CmsInterface;
use CBLib\Cms\CmsUserInterface;
use CBLib\Cms\Joomla\Joomla3\CmsUser;
use CBLib\Application\ApplicationContainerInterface;
use CBLib\Cms\Joomla\Joomla3\CmsEventHandler;
use CBLib\Input\InputInterface;
use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\Joomla3 Class implementation
 * 
 */
class Joomla3 implements CmsInterface
{
	/**
	 * Constructor. Must define DI for CmsPermissions and RouterInterface
	 *
	 * @param  ApplicationContainerInterface  $di
	 */
	public function __construct( ApplicationContainerInterface $di )
	{
		$di->set( 'CBLib\Cms\CmsPermissionsInterface', 'CBLib\Cms\Joomla\Joomla3\CmsPermissions', true )
			->alias( 'CBLib\Cms\CmsPermissionsInterface', 'CmsPermissions' );

		$di->set( 'CBLib\Controller\RouterInterface', 'CBLib\Cms\Joomla\Joomla3\CmsRouter', true )
			->alias( 'CBLib\Controller\RouterInterface', 'Router' );

		/* This one is to work-around a bug in Joomla 3.3.6- that prevents using closures in observer objects:
		 * ( https://github.com/joomla/joomla-cms/pull/4865 )
		 */
		$di->set( 'CBLib\Cms\Joomla\Joomla3\CmsEventsRegistry', 'CBLib\Registry\Registry', true );
	}

	/**
	 * @param  string   $info  Informwation to return ('release' php-style version)
	 * @return string
	 */
	public function getCmsVersion( $info = 'release' )
	{
		switch ( $info ) {
			case 'release':
				return JVERSION;
			default:
				trigger_error( __CLASS__ . '::'. __FUNCTION__ . ': info not supported', E_USER_WARNING );
				return null;
		}
	}

	/**
	 * @param  ApplicationContainerInterface  $di
	 * @param  string                         $type    'Web' or 'Cli'
	 * @param  array|InputInterface           $input
	 * @return InputInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getInput( ApplicationContainerInterface $di, $type, $input )
	{
		// Standalone case without input:
		$srcGpc				=	( $input === null && $type == 'Web' );

		if ( $srcGpc ) {
			//TODO Check here how we could use in the future JInput (which has buggy getArray()).
			// $_REQUEST is needed here because of Joomla's SEF populating only $_REQUEST:
			$input 			=   array_merge( $_GET, $_POST, $_REQUEST );
		}

		// Standalone case continued or array for input:
		if ( is_array( $input ) ) {
			/** @see \CBLib\Input\Input::__construct() */
			return $di->get( 'CBLib\Input\Input', array( 'source' => $input, 'srcGp' => $srcGpc ) )
				->setNamespaceRegistry( 'get', $di->get( 'CBLib\Input\Input', array( 'source' => $_GET, 'srcGp' => $srcGpc ) ) )
				->setNamespaceRegistry( 'post', $di->get( 'CBLib\Input\Input', array( 'source' => $_POST, 'srcGp' => $srcGpc ) ) )
				->setNamespaceRegistry( 'files', $di->get( 'CBLib\Input\Input', array( 'source' => $_FILES, 'srcGp' => $srcGpc ) ) )
				->setNamespaceRegistry( 'cookie', $di->get( 'CBLib\Input\Input', array( 'source' => $_COOKIE, 'srcGp' => $srcGpc ) ) )
				->setNamespaceRegistry( 'server', $di->get( 'CBLib\Input\Input', array( 'source' => $_SERVER, 'srcGp' => $srcGpc ) ) )
				->setNamespaceRegistry( 'env', $di->get( 'CBLib\Input\Input', array( 'source' => $_ENV, 'srcGp' => $srcGpc ) ) );
		}

		// From now on it can only be an object:
		if ( ! is_object( $input ) ) {
			throw new \InvalidArgumentException('Invalid input argument in CBLib SetMainInput');
		}

		// Already InputInterface:
		if ( $input instanceof InputInterface ) {
			return $input;
		}

		/** This could be a way to get all inputs from Joomla, but it is not fast because no way to get just the keys or data:
		 *	if ( ! $input ) {
		 *		// This is not usable and filter is buggy in Joomla 3.3 unfortunately, so can't use:		$input		=	\JFactory::getApplication()->input->getArray();
		 *		$inputKeys		=	array_keys( \JFactory::getApplication()->input->getArray() );
		 *		$input			=	array();
		 *		foreach ( $inputKeys as $k ) {
		 *			$input[$k]	=	\JFactory::getApplication()->input->get( $k, null, 'raw' );
		 *		}
		 *	}
		 */

		/** @see \CBLib\Input\Input::__construct() */
		/** @var \JInput|\Traversable $input */
		return $di->get( 'CBLib\Input\Input', array( 'source' => $input ) );
	}

	/**
	 * Returns client id (0 = front, 1 = admin)
	 *
	 * @return int
	 */
	public function getClientId( )
	{
		return \JFactory::getApplication()->getClientId();
	}

	/**
	 * Returns language name
	 *
	 * @return int
	 */
	public function getLanguageName( )
	{
		return strtolower( preg_replace( '/^(\w+).*$/i', '\1', \JFactory::getLanguage()->getName() ) );
	}

	/**
	 * Returns language tags
	 *
	 * @return int
	 */
	public function getLanguageTag( )
	{
		return \JFactory::getLanguage()->getTag();
	}

	/**
	 * Returns extension name being executed (e.g. com_comprofiler or mod_cblogin)
	 *
	 * @return string
	 */
	public function getExtensionName( )
	{
		return Application::Input()->get( 'option', null, GetterInterface::COMMAND );

	}

	/**
	 * Get the CBLib's interface class to the CMS User
	 *
	 * @param  int|array|null $userIdOrCriteria  [optional] default: NULL: viewing user, int: User-id (0: guest), array: Criteria, e.g. array( 'username' => 'uniqueUsername' ) or array( 'email' => 'uniqueEmail' )
	 * @return CmsUserInterface|boolean           Boolean FALSE if user does not exist (and userId = 0)
	 */
	public function getCmsUser( $userIdOrCriteria )
	{
		return CmsUser::getInstance( $userIdOrCriteria );
	}

	/**
	 * Gets the folder with path for the $clientId (0 = front, 1 = admin)
	 *
	 * @param $clientId
	 * @return string
	 *
	 * @throws \UnexpectedValueException
	 */
	public function getFolderPath( $clientId )
	{
		$optionCleaned	=	$this->getExtensionName();

		if ( $clientId == 0 )
		{
			return JPATH_ROOT . '/components/' . $optionCleaned;
		}
		elseif ( $clientId == 1 )
		{
			return JPATH_ADMINISTRATOR . '/components/' . $optionCleaned;
		}
		throw new \UnexpectedValueException( 'Unexpected client id' );
	}

	/**
	 * Registers a handler to filter the final output
	 *
	 * @param  callable  $handler  A function( $body ) { return $bodyChanged; }
	 * @return self                To allow chaining.
	 */
	public function registerOnAfterRenderBodyFilter( $handler )
	{
		$this->registerEvent(
			'onAfterRender',
			function( ) use ( $handler ) {
				$app	=	\JFactory::getApplication();
				$app->setBody( $handler( $app->getBody() ) );
			}
		);

		return $this;
	}

	/**
	 * Registers a handler to a particular CMS event
	 *
	 * @param  string    $event    The event name:
	 * @param  callable  $handler  The handler, a function or an instance of a event object.
	 * @return self                To allow chaining.
	 */
	public function registerEvent( $event, $handler )
	{
		/** This line (and the class:
		 * @see CmsEventHandler
		 * is to work-around a bug in Joomla 3.3.6- that prevents using closures in observer objects:
		 * ( https://github.com/joomla/joomla-cms/pull/4865 )
		 */
		/** @noinspection PhpDeprecationInspection */
		CmsEventHandler::register( $event, $handler );

		/*
		 * This is the simple way of implementing this but does not work if $handler is a closure or a callable array( $class, $method ) where $class has closure variables:
		 * but because of https://github.com/joomla/joomla-cms/pull/4865 this can not be done:
		 *
		 *	\JFactory::getApplication()
		 *		->registerEvent( $event, $handler );
		 */

		return $this;
	}

	/**
	 * Prepares the HTML $htmlText with triggering CMS Content Plugins
	 *
	 * @param  string   $htmlText
	 * @return string
	 */
	public function prepareHtmlContentPlugins( $htmlText ) {
		$previousDocType	=	\JFactory::getDocument()->getType();

		\JFactory::getDocument()->setType( 'html' );

		jimport( 'joomla.application.module.helper' );

		try {
			$htmlText			=	 \JHtml::_( 'content.prepare', $htmlText );
		} catch ( \Exception $e ) {}

		\JFactory::getDocument()->setType( $previousDocType );

		return $htmlText;
	}

	/**
	 * Get CMS Database object
	 * @return object|\JDatabase|\JDatabaseDriver
	 */
	public function getCmsDatabaseDriver( )
	{
		return \JFactory::getDBO();
	}
}
