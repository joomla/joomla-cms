<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * GMail Authentication Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationGMail extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onAuthenticate($credentials, $options, &$response)
	{
		$message = '';
		$success = 0;
		if (function_exists('curl_init'))
		{
			if (strlen($credentials['username']) && strlen($credentials['password']))
			{
				$curl = curl_init('https://mail.google.com/mail/feed/atom');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($curl, CURLOPT_HEADER, 1);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_USERPWD, $credentials['username'].':'.$credentials['password']);
				$result = curl_exec($curl);
				$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

				switch($code)
				{
					case 200:
				 		$message = 'Access Granted';
				 		$success = 1;
					break;
					case 401:
						$message = 'Access Denied';
					break;
					default:
						$message = 'Result unknown, access denied.';
						break;
				}
			}
			else  {
				$message = 'Username or password blank';
			}
		}
		else {
			$message = 'curl isn\'t installed';
		}

		if ($success)
		{
			$response->status 	     = JAUTHENTICATE_STATUS_SUCCESS;
			$response->error_message = '';
			$response->email 	= $credentials['username'];
			$response->fullname = $credentials['username'];
		}
		else
		{
			$response->status 		= JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message	= 'Failed to authenticate: ' . $message;
		}
	}
}
