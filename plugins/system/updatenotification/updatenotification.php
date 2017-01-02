<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Uncomment the following line to enable debug mode (update notification email sent every single time)
// define('PLG_SYSTEM_UPDATENOTIFICATION_DEBUG', 1);

/**
 * Joomla! Update Notification plugin
 *
 * Sends out an email to all Super Users or a predefined email address when a new Joomla! version is available.
 *
 * This plugin is a direct adaptation of the corresponding plugin in Akeeba Ltd's Admin Tools. The author has
 * consented to relicensing their plugin's code under GPLv2 or later (the original version was licensed under
 * GPLv3 or later) to allow its inclusion in the Joomla! CMS.
 *
 * @since  3.5
 */
class PlgSystemUpdatenotification extends JPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.6.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * The update check and notification email code is triggered after the page has fully rendered.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onAfterRender()
	{
		// Get the timeout for Joomla! updates, as configured in com_installer's component parameters
		JLoader::import('joomla.application.component.helper');
		$component = JComponentHelper::getComponent('com_installer');

		/** @var \Joomla\Registry\Registry $params */
		$params        = $component->params;
		$cache_timeout = (int) $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if (!defined('PLG_SYSTEM_UPDATENOTIFICATION_DEBUG') && (abs($now - $last) < $cache_timeout))
		{
			return;
		}

		// Update last run status
		// If I have the time of the last run, I can update, otherwise insert
		$this->params->set('lastrun', $now);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
					->update($db->qn('#__extensions'))
					->set($db->qn('params') . ' = ' . $db->q($this->params->toString('JSON')))
					->where($db->qn('type') . ' = ' . $db->q('plugin'))
					->where($db->qn('folder') . ' = ' . $db->q('system'))
					->where($db->qn('element') . ' = ' . $db->q('updatenotification'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();

			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execite
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		// Abort on failure
		if (!$result)
		{
			return;
		}

		// This is the extension ID for Joomla! itself
		$eid = 700;

		// Get any available updates
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates(array($eid), $cache_timeout);

		// If there are no updates our job is done. We need BOTH this check AND the one below.
		if (!$results)
		{
			return;
		}

		// Unfortunately Joomla! MVC doesn't allow us to autoload classes
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models', 'InstallerModel');

		// Get the update model and retrieve the Joomla! core updates
		$model = JModelLegacy::getInstance('Update', 'InstallerModel');
		$model->setState('filter.extension_id', $eid);
		$updates = $model->getItems();

		// If there are no updates we don't have to notify anyone about anything. This is NOT a duplicate check.
		if (empty($updates))
		{
			return;
		}

		// Get the available update
		$update = array_pop($updates);

		// Check the available version. If it's the same as the installed version we have no updates to notify about.
		if (version_compare($update->version, JVERSION, 'eq'))
		{
			return;
		}

		// If we're here, we have updates. First, get a link to the Joomla! Update component.
		$baseURL  = JUri::base();
		$baseURL  = rtrim($baseURL, '/');
		$baseURL .= (substr($baseURL, -13) !== 'administrator') ? '/administrator/' : '/';
		$baseURL .= 'index.php?option=com_joomlaupdate';
		$uri      = new JUri($baseURL);

		/**
		 * Some third party security solutions require a secret query parameter to allow log in to the administrator
		 * backend of the site. The link generated above will be invalid and could probably block the user out of their
		 * site, confusing them (they can't understand the third party security solution is not part of Joomla! proper).
		 * So, we're calling the onBuildAdministratorLoginURL system plugin event to let these third party solutions
		 * add any necessary secret query parameters to the URL. The plugins are supposed to have a method with the
		 * signature:
		 *
		 * public function onBuildAdministratorLoginURL(JUri &$uri);
		 *
		 * The plugins should modify the $uri object directly and return null.
		 */

		JEventDispatcher::getInstance()->trigger('onBuildAdministratorLoginURL', array(&$uri));

		// Let's find out the email addresses to notify
		$superUsers    = array();
		$specificEmail = $this->params->get('email', '');

		if (!empty($specificEmail))
		{
			$superUsers = $this->getSuperUsers($specificEmail);
		}

		if (empty($superUsers))
		{
			$superUsers = $this->getSuperUsers();
		}

		if (empty($superUsers))
		{
			return;
		}

		/* Load the appropriate language. We try to load English (UK), the current user's language and the forced
		 * language preference, in this order. This ensures that we'll never end up with untranslated strings in the
		 * update email which would make Joomla! seem bad. So, please, if you don't fully understand what the
		 * following code does DO NOT TOUCH IT. It makes the difference between a hobbyist CMS and a professional
		 * solution! */
		$jLanguage = JFactory::getLanguage();
		$jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, 'en-GB', true, true);
		$jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, null, true, false);

		// Then try loading the preferred (forced) language
		$forcedLanguage = $this->params->get('language_override', '');

		if (!empty($forcedLanguage))
		{
			$jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
		}

		// Set up the email subject and body

		$email_subject = JText::_('PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_SUBJECT');
		$email_body    = JText::_('PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_BODY');

		// Replace merge codes with their values
		$newVersion = $update->version;

		$jVersion       = new JVersion;
		$currentVersion = $jVersion->getShortVersion();

		$jConfig  = JFactory::getConfig();
		$sitename = $jConfig->get('sitename');
		$mailFrom = $jConfig->get('mailfrom');
		$fromName = $jConfig->get('fromname');

		$substitutions = array(
			'[NEWVERSION]' => $newVersion,
			'[CURVERSION]' => $currentVersion,
			'[SITENAME]'   => $sitename,
			'[URL]'        => JUri::base(),
			'[LINK]'       => $uri->toString(),
			'\\n'          => "\n",
		);

		foreach ($substitutions as $k => $v)
		{
			$email_subject = str_replace($k, $v, $email_subject);
			$email_body    = str_replace($k, $v, $email_body);
		}

		// Send the emails to the Super Users
		foreach ($superUsers as $superUser)
		{
			$mailer = JFactory::getMailer();
			$mailer->setSender(array($mailFrom, $fromName));
			$mailer->addRecipient($superUser->email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);
			$mailer->Send();
		}
	}

	/**
	 * Returns the Super Users email information. If you provide a comma separated $email list
	 * we will check that these emails do belong to Super Users and that they have not blocked
	 * system emails.
	 *
	 * @param   null|string  $email  A list of Super Users to email
	 *
	 * @return  array  The list of Super User emails
	 *
	 * @since   3.5
	 */
	private function getSuperUsers($email = null)
	{
		// Get a reference to the database object
		$db = JFactory::getDbo();

		// Convert the email list to an array
		if (!empty($email))
		{
			$temp   = explode(',', $email);
			$emails = array();

			foreach ($temp as $entry)
			{
				$entry    = trim($entry);
				$emails[] = $db->q($entry);
			}

			$emails = array_unique($emails);
		}
		else
		{
			$emails = array();
		}

		// Get a list of groups which have Super User privileges
		$ret = array();

		try
		{
			$rootId    = JTable::getInstance('Asset', 'JTable')->getRootId();
			$rules     = JAccess::getAssetRules($rootId)->getData();
			$rawGroups = $rules['core.admin']->getData();
			$groups    = array();

			if (empty($rawGroups))
			{
				return $ret;
			}

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->q($g);
				}
			}

			if (empty($groups))
			{
				return $ret;
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user IDs of users belonging to the SA groups
		try
		{
			$query = $db->getQuery(true)
						->select($db->qn('user_id'))
						->from($db->qn('#__user_usergroup_map'))
						->where($db->qn('group_id') . ' IN(' . implode(',', $groups) . ')');
			$db->setQuery($query);
			$rawUserIDs = $db->loadColumn(0);

			if (empty($rawUserIDs))
			{
				return $ret;
			}

			$userIDs = array();

			foreach ($rawUserIDs as $id)
			{
				$userIDs[] = $db->q($id);
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Administrator users
		try
		{
			$query = $db->getQuery(true)
						->select(
							array(
								$db->qn('id'),
								$db->qn('username'),
								$db->qn('email'),
							)
						)->from($db->qn('#__users'))
						->where($db->qn('id') . ' IN(' . implode(',', $userIDs) . ')')
						->where($db->qn('block') . ' = 0')
						->where($db->qn('sendEmail') . ' = ' . $db->q('1'));

			if (!empty($emails))
			{
				$query->where($db->qn('email') . 'IN(' . implode(',', $emails) . ')');
			}

			$db->setQuery($query);
			$ret = $db->loadObjectList();
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = JFactory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
