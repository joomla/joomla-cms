<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Joomla! System Logging Plugin
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemLog extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.5
	 */
	function plgSystemLog(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	function onLoginFailure($response)
	{
		jimport('joomla.utilities.log');

		$log = JLog::getInstance();
		$errorlog = array();

		switch($response['status'])
	    {
			case JAUTHENTICATE_STATUS_CANCEL :
			{
				$errorlog['status']  = $response['type'] . " CANCELED: ";
				$errorlog['comment'] = $response['error_message'];
				$log->addEntry($errorlog);
			} break;

			case JAUTHENTICATE_STATUS_FAILURE :
			{
				$errorlog['status']  = $response['type'] . " FAILURE: ";
				$errorlog['comment'] = $response['error_message'];
				$log->addEntry($errorlog);
			}	break;

			default :
			{
				$errorlog['status']  = $response['type'] . " UNKNOWN ERROR: ";
				$errorlog['comment'] = $response['error_message'];
				$log->addEntry($errorlog);
			}	break;
		}
	}
}