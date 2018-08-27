<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemActionLogs extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Import actionlog plugin group so that these plugins will be triggered for events
		JPluginHelper::importPlugin('actionlog');
	}

	/**
	 * Adds additional fields to the user editing form for logs e-mail notifications
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$formName = $form->getName();

		$allowedFormNames = array(
			'com_users.profile',
			'com_admin.profile',
			'com_users.user',
		);

		if (!in_array($formName, $allowedFormNames))
		{
			return true;
		}

		/**
		 * We only allow users who has Super User permission change this setting for himself or for other users
		 * who has same Super User permission
		 */

		$user = JFactory::getUser();

		if (!$user->authorise('core.admin'))
		{
			return true;
		}

		// If we are on the save command, no data is passed to $data variable, we need to get it directly from request
		$jformData = $this->app->input->get('jform', array(), 'array');

		if ($jformData && !$data)
		{
			$data = $jformData;
		}

		if (is_array($data))
		{
			$data = (object) $data;
		}

		if (!empty($data->id) && !JUser::getInstance($data->id)->authorise('core.admin'))
		{
			return true;
		}

		JForm::addFormPath(dirname(__FILE__) . '/forms');
		$form->loadFile('actionlogs', false);
	}

	/**
	 * Runs after the HTTP response has been sent to the client and delete log records older than certain days
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRespond()
	{
		$daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);

		if ($daysToDeleteAfter <= 0)
		{
			return;
		}

		// The delete frequency will be once per day
		$deleteFrequency = 3600 * 24;

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if (abs($now - $last) < $deleteFrequency)
		{
			return;
		}

		// Update last run status
		$this->params->set('lastrun', $now);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($this->params->toString('JSON')))
			->where($db->qn('type') . ' = ' . $db->q('plugin'))
			->where($db->qn('folder') . ' = ' . $db->q('system'))
			->where($db->qn('element') . ' = ' . $db->q('actionlogs'));

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

		$daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);

		if ($daysToDeleteAfter > 0)
		{
			$conditions = array($db->quoteName('log_date') . ' < DATE_SUB(NOW(), INTERVAL ' . $daysToDeleteAfter . ' DAY)');

			$query->clear()
				->delete($db->quoteName('#__action_logs'))->where($conditions);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				// Ignore it
				return;
			}
		}
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
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
