<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
class PlgSystemActionLogs extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Import actionlog plugin group so that these plugins will be triggered for events
		PluginHelper::importPlugin('actionlog');
	}

	/**
	 * Adds additional fields to the user editing form for logs e-mail notifications
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof Form)
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

		$user = Factory::getUser();

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

		if (empty($data->id) || !User::getInstance($data->id)->authorise('core.admin'))
		{
			return true;
		}

		Form::addFormPath(dirname(__FILE__) . '/forms');

		if ((!PluginHelper::isEnabled('actionlog', 'joomla')) && (Factory::getApplication()->isAdmin()))
		{
			$form->loadFile('information', false);

			return true;
		}

		if (!PluginHelper::isEnabled('actionlog', 'joomla'))
		{
			return true;
		}

		$form->loadFile('actionlogs', false);
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (!in_array($context, array('com_users.profile', 'com_admin.profile', 'com_users.user')))
		{
			return true;
		}

		if (is_array($data))
		{
			$data = (object) $data;
		}

		if (!User::getInstance($data->id)->authorise('core.admin'))
		{
			return true;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('notify', 'extensions')))
			->from($this->db->quoteName('#__action_logs_users'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $data->id);

		try
		{
			$values = $this->db->setQuery($query)->loadObject();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		if (!$values)
		{
			return true;
		}

		$data->actionlogs                       = new StdClass;
		$data->actionlogs->actionlogsNotify     = $values->notify;
		$data->actionlogs->actionlogsExtensions = $values->extensions;

		return true;
	}

	/**
	 * Runs after the HTTP response has been sent to the client and delete log records older than certain days
	 *
	 * @return  void
	 *
	 * @since   3.9.0
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
		$now = $db->quote(Factory::getDate()->toSql());

		if ($daysToDeleteAfter > 0)
		{
			$conditions = array($db->quoteName('log_date') . ' < ' . $query->dateAdd($now, -1 * $daysToDeleteAfter, ' DAY'));

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
	 * Utility method to act on a user after it has been saved.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isNew    True if a new user is stored.
	 * @param   boolean  $success  True if user was successfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onUserAfterSave($user, $isNew, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		// Clear access rights in case user groups were changed.
		$userObject = Factory::getUser($user['id']);
		$userObject->clearAccessRights();
		$authorised = $userObject->authorise('core.admin');

		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from($this->db->quoteName('#__action_logs_users'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user['id']);

		try
		{
			$exists = (bool) $this->db->setQuery($query)->loadResult();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		// If preferences don't exist, insert.
		if (!$exists && $authorised && isset($user['actionlogs']))
		{
			$values  = array((int) $user['id'], (int) $user['actionlogs']['actionlogsNotify']);
			$columns = array('user_id', 'notify');

			if (isset($user['actionlogs']['actionlogsExtensions']))
			{
				$values[]  = $this->db->quote(json_encode($user['actionlogs']['actionlogsExtensions']));
				$columns[] = 'extensions';
			}

			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__action_logs_users'))
				->columns($this->db->quoteName($columns))
				->values(implode(',', $values));
		}
		elseif ($exists && $authorised && isset($user['actionlogs']))
		{
			// Update preferences.
			$values = array($this->db->quoteName('notify') . ' = ' . (int) $user['actionlogs']['actionlogsNotify']);

			if (isset($user['actionlogs']['actionlogsExtensions']))
			{
				$values[] = $this->db->quoteName('extensions') . ' = ' . $this->db->quote(json_encode($user['actionlogs']['actionlogsExtensions']));
			}

			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__action_logs_users'))
				->set($values)
				->where($this->db->quoteName('user_id') . ' = ' . (int) $user['id']);
		}
		elseif ($exists && !$authorised)
		{
			// Remove preferences if user is not authorised.
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__action_logs_users'))
				->where($this->db->quoteName('user_id') . ' = ' . (int) $user['id']);
		}

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes user preferences
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__action_logs_users'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user['id']);

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = Factory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $clientId)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => $clientId ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = Cache::getInstance('callback', $options);
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
