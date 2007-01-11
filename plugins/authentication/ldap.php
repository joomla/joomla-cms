<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	JFramework
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.plugin.helper');
jimport('joomla.client.ldap');

/**
 * LDAP Authenticate Plugin
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class JAuthenticateLdap extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function JAuthenticateLdap(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	string	$username	Username for authentication
	 * @param	string	$password	Password for authentication
	 * @return	object	JAuthenticateResponse
	 * @since 1.5
	 */
	function onAuthenticate( $username, $password )
	{
		global $mainframe;

		// Initialize variables
		$conditions = '';
		$success = false;

		// Get a database connector
		$db =& JFactory::getDBO();

		// load plugin parameters
	 	$plugin =& JPluginHelper::getPlugin('authentication', 'ldap');
	 	$params = new JParameter( $plugin->params );

		$ldap	= new JLDAP($params);
		$result = new JAuthenticateResponse('LDAP');

		if (!$ldap->connect())
		{
			//die('Unable to connect to ldap server');
			$result->status = JAUTHENTICATE_STATUS_FAILURE;
			$result->error_message = 'Unable to connect to LDAP server';
			return $result;
		}

		$auth_method = $params->get('auth_method');

		switch($auth_method)
		{
			case 'anonymous':
			{
				// Need to do some work!
				if($ldap->anonymous_bind())
				{
					// Comparison time
					$success = $ldap->compare(str_replace("[username]",$username,$params->get('users_dn')),$params->get('ldap_password'),$password);
				}
				else
				{
					//die('Anonymous bind failed');
					$result->status = JAUTHENTICATE_STATUS_FAILURE;
					$result->error_message = 'Anonymous bind failed.';
					return $result;
				}
			}	break;

			case 'bind':
			{
				// We just accept the result here
					$success = $ldap->bind($username,$password);
			}	break;

			case 'authbind':
			{	// First bind as a search enabled account
				if($ldap->bind())
				{
					$ldap_uid = $params->get('ldap_uid');
					$userdetails = $ldap->simple_search($params->get('ldap_uid').'='.$username);
					if(isset($userdetails[0][$ldap_uid][0])) {
						$success = $ldap->bind($userdetails[0][dn], $password,1);
					}
				}
			}	break;

			case 'authenticated':
			{
				if($ldap->bind())
				{
					// Comparison time
					$success = $ldap->compare(str_replace("[username]",$username,$params->get('users_dn')),$params->get('ldap_password'),$password);
				}
				else
				{
					//die('Authenticated Bind Failed');
					$result->status = JAUTHENTICATE_STATUS_FAILURE;
					$result->error_message = 'Authenticated bind failed.';
					return $result;
				}
			}	break;
		}

		if(!$success)
		{
			$result->status = JAUTHENTICATE_STATUS_FAILURE;
			$result->error_message = 'Failed to bind to LDAP server';
		}
		else
		{
			$result->status = JAUTHENTICATE_STATUS_SUCCESS;

			$userdetails 	= $ldap->simple_search(str_replace("[search]", $username, $params->get('search_string')));
			$ldap_email 	= $params->get('ldap_email');
			$ldap_fullname	= $params->get('ldap_fullname');

			if (isset($userdetails[0][$ldap_email][0]))
			{
				$result->email = $userdetails[0][$ldap_email][0];

				if(isset($userdetails[0][$ldap_fullname][0])) {
					$result->fullname = $userdetails[0][$ldap_fullname][0];
				} else {
					$result->fullname = $username;
				}
			}
		}

		$ldap->close();
		return $result;
	}
}
?>
