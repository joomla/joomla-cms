<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Client;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for ClientHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 * @since       3.4
 * @deprecated  4.0 Will be removed without replacement
 */
class ClientWrapper
{
	/**
	 * Helper wrapper method for getCredentials
	 *
	 * @param   string   $client  Client name, currently only 'ftp' is supported
	 * @param   boolean  $force   Forces re-creation of the login credentials. Set this to
	 *
	 * @return  array    Client layer configuration options, consisting of at least
	 *
	 * @see     ClientHelper::getCredentials()
	 * @since   3.4
	 */
	public function getCredentials($client, $force = false)
	{
		return ClientHelper::getCredentials($client, $force);
	}

	/**
	 * Helper wrapper method for setCredentials
	 *
	 * @param   string  $client  Client name, currently only 'ftp' is supported
	 * @param   string  $user    Username
	 * @param   string  $pass    Password
	 *
	 * @return boolean  True if the given login credentials have been set and are valid
	 *
	 * @see     ClientHelper::setCredentials()
	 * @since   3.4
	 */
	public function setCredentials($client, $user, $pass)
	{
		return ClientHelper::setCredentials($client, $user, $pass);
	}

	/**
	 * Helper wrapper method for hasCredentials
	 *
	 * @param   string  $client  Client name, currently only 'ftp' is supported
	 *
	 * @return boolean  True if login credentials are available
	 *
	 * @see     ClientHelper::hasCredentials()
	 * @since   3.4
	 */
	public function hasCredentials($client)
	{
		return ClientHelper::hasCredentials($client);
	}

	/**
	 * Helper wrapper method for setCredentialsFromRequest
	 *
	 * @param   string  $client  The name of the client.
	 *
	 * @return  mixed  True, if FTP settings; JError if using legacy tree
	 *
	 * @see     UserHelper::setCredentialsFromRequest()
	 * @since   3.4
	 * @throws  \InvalidArgumentException if credentials invalid
	 */
	public function setCredentialsFromRequest($client)
	{
		return ClientHelper::setCredentialsFromRequest($client);
	}
}
