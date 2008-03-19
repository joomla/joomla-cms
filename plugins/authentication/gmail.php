<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * GMail Authentication Plugin
 *
 * @author Samuel Moffatt <sam.moffatt@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationGMail extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgAuthenticationGMail(& $subject, $config) {
		parent::__construct($subject, $config);
	}

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
	function onAuthenticate( $credentials, $options, &$response )
	{
		$message = '';
		$success = 0;
		if(function_exists('curl_init'))
		{
			if(strlen($credentials['username']) && strlen($credentials['password']))
			{
				$curl = curl_init('https://mail.google.com/mail/feed/atom');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
			$message = 'curl isn\'t insalled';
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
