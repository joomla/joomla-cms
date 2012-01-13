<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Joomla! System Logging Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.log
 */
class  plgSystemLog extends JPlugin
{
	function onUserLoginFailure($response)
	{
		$log = JLog::getInstance();
		$errorlog = array();

		switch($response['status'])
		{
			case JAuthentication::STATUS_SUCCESS :
			{
				$errorlog['status']  = $response['type'] . " CANCELED: ";
				$errorlog['comment'] = $response['error_message'];
				$log->addEntry($errorlog);
			} break;

			case JAuthentication::STATUS_FAILURE :
			{
				$errorlog['status']  = $response['type'] . " FAILURE: ";
				if ($this->params->get('log_username', 0)) {
					$errorlog['comment'] = $response['error_message'] . ' ("' . $response['username'] . '")';
				}
				else {
					$errorlog['comment'] = $response['error_message'];
				}
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
