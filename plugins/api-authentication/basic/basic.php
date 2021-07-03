<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.joomla
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

/**
 * Joomla Authentication plugin
 *
 * @since  4.0.0
 */
class PlgApiAuthenticationBasic extends CMSPlugin
{
	/**
	 * The application object
	 *
	 * @var    \Joomla\CMS\Application\CMSApplicationInterface
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * The application object
	 *
	 * @var    \Joomla\Database\DatabaseInterface
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$response->type = 'Basic';

		$username = $this->app->input->server->get('PHP_AUTH_USER', '', 'USERNAME');
		$password = $this->app->input->server->get('PHP_AUTH_PW', '', 'RAW');

		if ($password === '')
		{
			$response->status        = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'password']))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = :username')
			->bind(':username', $username);

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			$match = UserHelper::verifyPassword($password, $result->password, $result->id);

			if ($match === true)
			{
				// Bring this in line with the rest of the system
				$user               = User::getInstance($result->id);
				$response->email    = $user->email;
				$response->fullname = $user->name;
				$response->username = $username;

				if ($this->app->isClient('administrator'))
				{
					$response->language = $user->getParam('admin_language');
				}

				else
				{
					$response->language = $user->getParam('language');
				}

				$response->status        = Authentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				// Invalid password
				$response->status        = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}
		else
		{
			// Let's hash the entered password even if we don't have a matching user for some extra response time
			// By doing so, we mitigate side channel user enumeration attacks
			UserHelper::hashPassword($password);

			// Invalid user
			$response->status        = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
