<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Logging Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.log
 * @since       1.5
 */
class PlgSystemLog extends JPlugin
{
	public function onUserLoginFailure($response)
	{
		$errorlog = array();

		switch($response['status'])
		{
			case JAuthentication::STATUS_SUCCESS:
				$errorlog['status']  = $response['type'] . " CANCELED: ";
				$errorlog['comment'] = $response['error_message'];
				break;

			case JAuthentication::STATUS_FAILURE:
				$errorlog['status']  = $response['type'] . " FAILURE: ";
				$errorlog['comment'] = $response['error_message'];
				if ($this->params->get('log_username', 0) && $this->params->get('log_sourceip,0))
				{
					$errorlog['comment'] .= ' (username="' . $response['username'] . '",srcip=$_SERVER["REMOTE_ADDR"])';
				}
				elseif ($this->params->get('log_username', 0) && $this->params->get('log_sourceip,1))
				{
					$errorlog['comment'] .= ' (username="' . $response['username'] . '")';
				}
				break;

			default:
				$errorlog['status']  = $response['type'] . " UNKNOWN ERROR: ";
				$errorlog['comment'] = $response['error_message'];
				break;
		}
		JLog::addLogger(array(), JLog::INFO);
		JLog::add($errorlog['comment'], JLog::INFO, $errorlog['status']);
	}
}
