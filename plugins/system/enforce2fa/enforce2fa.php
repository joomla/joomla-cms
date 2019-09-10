<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Enforce Two Factor Authentication Class
 *
 * @since  4.0.0
 */
class PlgSystemEnforce2fa extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database driver
	 *
	 * @var   DatabaseDriver
	 * @since 4.0.0
	 */
	protected $db;

	/**
	 * Application object
	 *
	 * @var   AdministratorApplication
	 * @since 4.0.0
	 */
	protected $app;

	/**
	 * Gets called after the user wanted to direct to a different page
	 * checks if user needs to be redirected and does so if required
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 *
	 * @throws Exception
	 */
	public function onAfterRoute(): void
	{

		if ($this->enforce2FA())
		{
			$this->redirect();
		}
	}

	/**
	 * Redirects user to his Two Factor Authentication setup page
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	private function redirect(): void
	{
		$option = $this->app->input->getCmd('option');
		$task   = $this->app->input->get('task');
		$view   = $this->app->input->getString('view', '');
		$layout = $this->app->input->getString('layout', '');

		/*
		* If user is already on edit profile screen or press update/apply button,
		* do nothing to avoid infinite redirect
		*/
		if ($option == 'com_users' && in_array($task, array('profile.save', 'profile.apply', 'user.logout', 'user.menulogout'))
			|| ($option === 'com_users' && $view === 'profile' && $layout === 'edit')
			|| ($option === 'com_users' && $view === 'user' && $layout === 'edit')
			|| ($option === 'com_users' && in_array($task, ['user.save', 'user.edit', 'user.apply', 'user.logout', 'user.menulogout']))
			|| ($option === 'com_login' && in_array($task, ['save', 'edit', 'apply', 'logout', 'menulogout'])))
		{
			return;
		}

		$this->loadLanguageEnforce2fa();

		// Redirect to com_users profile edit
		$this->app->enqueueMessage(Text::_('PLG_SYSTEM_ENFORCE2FA_REDIRECT_MESSAGE'), 'notice');

		if ($this->app->isClient('site'))
		{
			$link = 'index.php?option=com_users&view=profile&layout=edit';
		}

		if ($this->app->isClient('administrator'))
		{
			$userId = Factory::getUser()->id;
			$link   = 'index.php?option=com_users&task=user.edit&id=' . $userId;
		}

		$this->app->redirect(Route::_($link, false));
	}

	/**
	 * Loads the language files for the plugin
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	private function loadLanguageEnforce2fa(): void
	{
		$lang      = Factory::getLanguage();
		$extension = 'plg_system_enforce2fa';

		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
		|| $lang->load($extension, JPATH_PLUGINS . '/system/enforce2fa', null, false, true);
	}

	/**
	 * Checks if 2fa needs to be enforced
	 * if so returns ture
	 * else returns false
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	private function enforce2FA(): bool
	{
		$id = Factory::getUser()->id;

		if (!$id)
		{
			return false;
		}

		$enforce2FA = Factory::getConfig()->get('enforce_2fa', 0);

		if (!$enforce2FA)
		{
			return false;
		}

		if (!$this->checkEnabled2FAPlugins())
		{
			return false;
		}

		$userId                     = $this->app->getIdentity()->id;
		$enforceOptions             = Factory::getConfig()->get('enforce_2fa_options', 3);
		$pluginsSiteEnable          = false;
		$pluginsAdministratorEnable = false;
		$pluginOptions              = $this->getPluginParams();

		/*
		 * Sets and checks pluginOptions for Site and Administrator view depending on if any 2fa plugin is enabled for that view
		 */
		array_walk($pluginOptions,
			static function ($pluginOption) use (&$pluginsSiteEnable, &$pluginsAdministratorEnable)
			{
				$option  = new Registry($pluginOption);
				$section = $option->get('section');

				switch ($section)
				{
					case 1:
						$pluginsSiteEnable = true;
						break;
					case 2:
						$pluginsAdministratorEnable = true;
						break;
					case 3:
						$pluginsAdministratorEnable = true;
						$pluginsSiteEnable          = true;
				}
			}
		);

		if ($pluginsSiteEnable && $this->app->isClient('site'))
		{
			if (in_array($enforceOptions, [1, 3]))
			{
				return !$this->checkUserSetup2FA($userId);
			}
		}

		if ($pluginsAdministratorEnable && $this->app->isClient('administrator'))
		{
			if (in_array($enforceOptions, [2, 3]))
			{
				return !$this->checkUserSetup2FA($userId);
			}
		}

		return false;
	}

	/**
	 * Checks if otpKey and otep for a given user id are not empty
	 * if any one is empty returns false
	 * else returns true
	 *
	 * @param   int  $userId  Id of a user to check if user has setup 2fa
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
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
	 * @return  stdClass|null
	 *
	 * @since   4.0.0
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
	 * @return boolean
	 *
	 * @since  4.0.0
	 */
	private function checkEnabled2FAPlugins(): bool
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
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

	/**
	 * Gets the parameters extension_id and params out of the dater base for all enabled Two Factor Authentication plugins
	 *
	 * @return array
	 *
	 * @since  4.0.0
	 */
	private function getPluginParams(): ?array
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('enabled') . ' = 1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('twofactorauth'));
		$db->setQuery($query);

		return $db->loadColumn();
	}
}
