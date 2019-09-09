<?php
/**
 * @package    enforce2fa
 *
 * @author     Alexander Kirndörfer <a.kirndoerfer@gmail.com>
 * @copyright  [COPYRIGHT]
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://github.com/AlexKirndoerfer
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

class plgSystemEnforce2fa extends CMSPlugin
{
	protected $autoloadLanguage = true;
	protected $db;
	protected $app;

	/**
	 * Gets called after the user successfully logged in
	 * checks if user needs to be redirected and does so if required
	 *
	 * @return void
	 * @throws Exception
	 * @since 4.0.0
	 */
	public function onUserAfterLogin(): void
	{
		if($this->enforce2FA())
		{
			$this->redirect();
		}
	}

	/**
	 * Gets called after the user wanted to direct to a different page
	 * checks if user needs to be redirected and does so if required
	 *
	 * @return void
	 * @throws Exception
	 * @since 4.0.0
	 */
	public function onAfterRoute(): void
	{
		if($this->enforce2FA())
		{
			$this->redirect();
		}
	}

	/**
	 * Redirects user to his Two Factor Authentication setup page
	 *
	 * @return void
	 * @since 4.0.0
	 */
	private function redirect(): void
	{
		$option = $this->app->input->getCmd('option');
		$task   = $this->app->input->get('task');
		$view   = $this->app->input->getString('view', '');
		$layout = $this->app->input->getString('layout', '');

		/*
		* If user is already on edit profile screen or view privacy article
		* or press update/apply button, or logout, do nothing to avoid infinite redirect
		*/
		if ($option == 'com_users' && in_array($task, array('profile.save', 'profile.apply', 'user.logout', 'user.menulogout'))
			|| ($option == 'com_users' && $view == 'profile' && $layout == 'edit'))
		{
			return;
		}

		// Redirect to com_users profile edit
		$this->app->enqueueMessage($this->getRedirectMessage(), 'notice');
		$link = 'index.php?option=com_users&view=profile&layout=edit';
		$this->app->redirect(Route::_($link, false));
	}

	/**
	 * Checks if 2fa needs to be enforced
	 * if so returns ture
	 * else returns false
	 *
	 * @return  bool
	 * @throws Exception
	 * @since   4.0.0
	 */
	private function enforce2FA(): bool
	{
		$enforce2FA = Factory::getConfig()->get('enforce_2fa', 0);

		if (!$enforce2FA)
		{
			return false;
		}

		if (!$this->checkEnabled2FAPlugins())
		{
			return false;
		}

		$userId         = $this->app->getIdentity()->id;
		$enforceOptions = Factory::getConfig()->get('enforce_2fa_options', 3);

		switch ($enforceOptions)
		{
			case 1:
				if ($this->app->isClient('site'))
				{
					return !$this->checkUserSetup2FA($userId);
				}
				break;
			case 2:
				if ($this->app->isClient('administrator'))
				{
					return !$this->checkUserSetup2FA($userId);
				}
				break;

			case 3:
			default:
				if ($this->app->isClient('site') || Factory::getApplication()->isClient('administrator'))
				{
					return !$this->checkUserSetup2FA($userId);
				}
				break;
		}

		return false;
	}

	/**
	 * Checks if otpKey and otep for a given user id are not empty
	 * if any one is empty returns false
	 * else returns true
	 *
	 * @param   int  $userId
	 *
	 * @return bool
	 * @since 4.0.0
	 */
	private function checkUserSetup2FA(int $userId): bool
	{
		$result = $this->get2FA($userId);

		if (empty($result->otpKey) || empty($result->otep))
		{
			return false;
		}

		return true;
	}

	/**
	 * Gets the otpKey and otep parameters from the database for a specific user id
	 *
	 * @param   int  $userId
	 *
	 * @return stdClass|null
	 * @since 4.0.0
	 */
	private function get2FA(int $userId): ?\stdClass
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['otpKey', 'otep']))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = :userId')
			->bind(':userId', $userId, ParameterType::INTEGER);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Checks if any plugins for 2fa are enabled
	 * if so returns true
	 * else false
	 *
	 * @return bool
	 * @since 4.0.0
	 */
	private function checkEnabled2FAPlugins(): bool
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('enabled') . ' = 1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('twofactorauth'));
		$db->setQuery($query);
		$result = $db->loadColumn();

		if (empty($result))
		{
			return false;
		}

		return true;
	}
}
