<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 * @since       3.2
 */
class PlgAuthenticationCookie extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		JLoader::register('JAuthentication', JPATH_LIBRARIES . '/joomla/user/authentication.php');

		$response->type = 'Cookie';

		// Set cookie params.
		if (!empty($options['lifetime']) && !empty($options['length']) && !empty($options['secure']))
		{
			$response->lifetime = $options['lifetime'];
			$response->length = $options['length'];
			$response->secure = $options['secure'];
		}

		// Make sure there really is a user with this name and get the data for the session.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'username', 'password')))
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('username') . ' = ' . $this->db->quote($credentials['username']));

		$result = $this->db->setQuery($query)->loadObject();

		if ($result)
		{
			// Bring this in line with the rest of the system
			$user = JUser::getInstance($result->id);
			$cookieName = JUserHelper::getShortHashedUserAgent();

			// If there is no cookie, bail out
			if (!$this->app->input->cookie->get($cookieName))
			{
				return;
			}

			// Set response data.
			$response->username = $result->username;
			$response->email    = $user->email;
			$response->fullname = $user->name;
			$response->password = $result->password;
			$response->language = $user->getParam('language');

			// Set response status.
			$response->status        = JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';
		}
		else
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
