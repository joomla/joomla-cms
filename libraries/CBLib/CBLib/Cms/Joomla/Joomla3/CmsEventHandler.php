<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/14 11:52 AM $
* @package CBLib\Cms\Joomla\Joomla3
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla\Joomla3;

use CBLib\Application\Application;
use CBLib\Registry\ParamsInterface;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\Joomla3\PlgSystemEventHandler Class implementation
 *
 * This class is for internal use only and temporary:
 * This class works around the bug in Joomla 3.3.6- debug plugin that doesn't allow closures for handlers
 * ( https://github.com/joomla/joomla-cms/pull/4865 )
 */
class CmsEventHandler
{
	/** @var string */
	private $event;

	/** @var int */
	private $eventIndex;

	/** @var int[] */
	private static $registeredEvents;

	/**
	 * Private Constructor
	 *
	 * @param  string  $event
	 * @param  int     $eventIndex
	 */
	private function __construct( $event, $eventIndex )
	{
		$this->event			=	$event;
		$this->eventIndex		=	$eventIndex;

		\JFactory::getApplication()
			->registerEvent( $event, array( $this, 'callHandler' ) );
	}

	/**
	 * Registration Factory of event types
	 * Do not call directly. Call Application::Cms()->registerEvent() instead (or better a specific Cms function.
	 * @see CmsInterface::registerEvent()
	 * @deprecated Marking as such to never call directly
	 *
	 * @param  string    $event    Event-type to register
	 * @param  callable  $handler  Event-handler
	 */
	public static function register( $event, $handler )
	{
		if ( !isset( self::$registeredEvents[$event] ) ) {
			self::$registeredEvents[$event]	=	0;
		}

		$eventIndex							=	self::$registeredEvents[$event]++;

		new self( $event, $eventIndex );

		self::registry()
			->set( self::registryKey( $event, $eventIndex ), $handler );
	}

	/**
	 * Call the handler
	 * Internal function for Joomla Event to call $this->callHandler, which dispatches to the CB handler
	 * Do not call directly
	 * @deprecated Marking as such to never call directly
	 *
	 * @return mixed
	 */
	public function callHandler( )
	{
		$registry			=	self::registry();
		$key				=	self::registryKey( $this->event, $this->eventIndex );

		if ( ! $registry->has( $key ) ) {
			return null;
		}

		$handler			=	$registry->get( $key );

		return call_user_func_array( $handler, func_get_args() );
	}

	/**
	 * Returns the registry of handlers
	 *
	 * @return ParamsInterface
	 */
	private static function registry( )
	{
		return Application::Application()->get( 'CBLib\Cms\Joomla\Joomla3\CmsEventsRegistry' );
	}

	/**
	 * Returns key to registry storing handlers
	 *
	 * @param  string  $event       Event type
	 * @param  int     $eventIndex  Index of the event type
	 * @return string               Key to registry
	 */
	private static function registryKey( $event, $eventIndex )
	{
		return $event . '.' . $eventIndex;
	}
}
