<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Logging Plugin.
 *
 * @since  1.5
 */
class PlgSystemLog extends JPlugin
{
	/**
	 * Called if user fails to be logged in.
	 *
	 * @param   array  $response  Array of response data.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onUserLoginFailure($response)
	{
		$errorlog = array();

		switch ($response['status'])
		{
			case JAuthentication::STATUS_SUCCESS:
				$errorlog['status']  = $response['type'] . ' CANCELED: ';
				$errorlog['comment'] = $response['error_message'];
				break;

			case JAuthentication::STATUS_FAILURE:
				$errorlog['status']  = $response['type'] . ' FAILURE: ';

				if ($this->params->get('log_username', 0))
				{
					$errorlog['comment'] = $response['error_message'] . ' ("' . $response['username'] . '")';
				}
				else
				{
					$errorlog['comment'] = $response['error_message'];
				}
				break;

			default:
				$errorlog['status']  = $response['type'] . ' UNKNOWN ERROR: ';
				$errorlog['comment'] = $response['error_message'];
				break;
		}

		JLog::addLogger(array(), JLog::INFO);
		try
		{
			JLog::add($errorlog['comment'], JLog::INFO, $errorlog['status']);
		}
		catch (Exception $e) 
		{
			// If the log file is unwriteable during login then we should not go to the error page
			return;
		}
	}
}
