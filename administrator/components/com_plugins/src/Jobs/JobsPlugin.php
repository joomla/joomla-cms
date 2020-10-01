<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Jobs;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Abstract Jobs Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JobsPlugin extends CMSPlugin
{

	/**
	 * Exit Code For no time to run
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_NO_TIME = 1;

	/**
	 * Exit Code For lock failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_NO_LOCK = 2;

	/**
	 * Exit Code For execution failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const JOB_KO_RUN = 3;

	/**
	 * Exit Code For execution success
	 * @since
	 */
	public const JOB_OK_RUN = 0;

	/**
	 * The status of the process
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $snapshot = [];

	/**
	 * Database object.
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * The Application object
	 *
	 * @var    JApplicationSite
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Get a pseudo Lock to the row.
	 *
	 * @param   string   $name    The plugin name.
	 * @param   string   $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string   $params  The plugin params.
	 * @param   boolean  $force   Force execution.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function acquireLock($name, $type, $params, $force = false) : bool
	{
		$lastrun = $params->get('lastrun', 0);

		// Get the timeout for Joomla! job plugin task
		$now     = time();
		$timeout = (int) $params->get('timeout', 1);
		$unit    = (int) $params->get('unit', 86400);
		$timeout = ($unit * $timeout);

		$eid = PluginHelper::getPlugin($type, $name);

		$this->snapshot['eid'] = $eid->id;
		$this->snapshot['job'] = $name;
		$this->snapshot['status'] = self::JOB_NO_TIME;
		$this->snapshot['duration'] = 0;

		if (((abs($now - $lastrun) < $timeout)) && (!$force))
		{
			// It's not time to run
			return false;
		}

		if (!$this->setLocked($name, $type))
		{
			$this->snapshot['status'] = self::JOB_NO_LOCK;

			return false;
		}

		$this->snapshot['status'] = self::JOB_OK_RUN;

		return true;
	}

	/**
	 * Pseudo Lock the row.
	 *
	 * @param   string  $name  The plugin name.
	 * @param   string  $type  The plugin type, relates to the subdirectory in the plugins directory.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setLocked($name, $type) : bool
	{
		$db = $this->db;

		try
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__jobs'))
				->values(':element, :folder')
				->bind(':element', $name)
				->bind(':folder', $type);

			$db->setQuery($query);
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			// Ignore it
			return false;
		}

		return true;
	}

	/**
	 * Pseudo unLock the row.
	 *
	 * @param   string  $name    The plugin name.
	 * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function releaseLock($name, $type) : bool
	{
		$db = $this->db;

		try
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__jobs'))
				->where($db->quoteName('element') . ' = :element')
				->where($db->quoteName('folder') . ' = :folder')
				->bind(':element', $name)
				->bind(':folder', $type);

			$db->setQuery($query);
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			// Ignore it
			$this->snapshot['status'] = self::JOB_KO_RUN;
			$this->snapshot['duration'] = 0;

			return false;
		}

		$taskid   = null;

		$query = $db->getQuery(true);
		$query->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = :element')
			->where($db->quoteName('folder') . ' = :folder')
			->bind(':element', $name)
			->bind(':folder', $type);

		$db->setQuery($query);

		$params = $db->loadColumn();
		$query  = $db->getQuery(true);
		$now    = Factory::getDate()->toSql();
		$query->update($db->quoteName('#__extensions'));

		// Update last run and taskid
		$taskParams = json_decode($params[0], true);
		$taskid = $taskParams['taskid'];

		$taskid++;
		$registry = new Registry($taskParams);
		$registry->set('taskid', $taskid);
		$registry->set('lastrun', time());
		$jsonparam = $registry->toString('JSON');

		$query->set($db->quoteName('params') . ' = :params')
			->where($db->quoteName('element') . ' = :element')
			->where($db->quoteName('folder') . ' = :folder')
			->bind(':params', $jsonparam)
			->bind(':element', $name)
			->bind(':folder', $type);

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			// If we failed to execute
			return false;
		}

		$result = (int) $db->getAffectedRows();

		if ($result === 0)
		{
			return false;
		}

		if ($this->_name === 'scheduler')
		{
			return true;
		}

		$endTime    = microtime(true);
		$this->snapshot['duration'] = sprintf('%0.2f', $endTime - $this->snapshot['startTime']);

		Log::add(
			Text::sprintf('PLG_JOB_' . strtoupper($this->_name) . '_END', $this->_name) .
			Text::sprintf('PLG_JOB_' . strtoupper($this->_name) . '_TASK', $taskid) .
			Text::sprintf('PLG_JOB_' . strtoupper($this->_name) . '_PROCESS_RESULT', $this->snapshot['status']) .
			Text::sprintf('PLG_JOB_' . strtoupper($this->_name) . '_PROCESS_COMPLETE', $this->snapshot['duration']),
			Log::INFO,
			'scheduler'
		);

		return true;
	}
}
