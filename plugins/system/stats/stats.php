<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Statistics system plugin. This sends anonymous data back to the Joomla! Project about the
 * PHP, SQL, Joomla and OS versions
 *
 * @since  3.5
 */
class PlgSystemStats extends JPlugin
{
	/**
	 * @const  integer
	 * @since  3.5
	 */
	const MODE_ALLOW_ALWAYS = 1;

	/**
	 * @const  integer
	 * @since  3.5
	 */
	const MODE_ALLOW_ONCE = 2;

	/**
	 * @const  integer
	 * @since  3.5
	 */
	const MODE_ALLOW_NEVER = 3;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.5
	 */
	protected $db;

	/**
	 * Unique identifier for this site
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $uniqueId;

	/**
	 * Listener for the `onAfterInitialise` event
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onAfterInitialise()
	{
		if (!$this->app->isAdmin() || !$this->isAllowedUser() || !$this->isUpdateRequired())
		{
			return;
		}

		$mode = (int) $this->params->get('mode');

		// Plugin parameters are saved and send always enabled
		if ($mode === static::MODE_ALLOW_ALWAYS)
		{
			try
			{
				$this->sendStats();
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'stats');
			}

			return;
		}

		JHtml::_('jquery.framework');
		JHtml::script('plg_system_stats/stats.js', false, true, false);
	}

	/**
	 * Get the user message rendered
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function onAjaxRenderStatsMessage()
	{
		if (!$this->isAllowedUser() || !$this->isAjaxRequest())
		{
			throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		return array(
			'html' => $this->getRenderer('message')->render($this->getLayoutData())
		);
	}

	/**
	 * User selected to always send data
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onAjaxSendAlways()
	{
		if (!$this->isAllowedUser() || !$this->isAjaxRequest())
		{
			throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		$this->params->set('mode', static::MODE_ALLOW_ALWAYS);

		if (!$this->saveParams())
		{
			throw new RuntimeException('Unable to save plugin settings', 500);
		}

		$this->sendStats();
	}

	/**
	 * User selected to never send data.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onAjaxSendNever()
	{
		if (!$this->isAllowedUser() || !$this->isAjaxRequest())
		{
			throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		$this->params->set('mode', static::MODE_ALLOW_NEVER);

		if (!$this->saveParams())
		{
			throw new RuntimeException('Unable to save plugin settings', 500);
		}
	}

	/**
	 * User selected to send data once.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onAjaxSendOnce()
	{
		if (!$this->isAllowedUser() || !$this->isAjaxRequest())
		{
			throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		$this->params->set('mode', static::MODE_ALLOW_ONCE);

		if (!$this->saveParams())
		{
			throw new RuntimeException('Unable to save plugin settings', 500);
		}

		$this->sendStats();
	}

	/**
	 * Get the data for the layout
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutData()
	{
		return array(
			'plugin'       => $this,
			'pluginParams' => $this->params,
			'statsData'    => $this->getStatsData()
		);
	}

	/**
	 * Get the layout paths
	 *
	 * @return  array()
	 *
	 * @since   3.5
	 */
	protected function getLayoutsPaths()
	{
		$template = JFactory::getApplication()->getTemplate();

		return array(
			JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/' . $this->_type . '/' . $this->_name,
			__DIR__ . '/layouts',
		);
	}

	/**
	 * Get the plugin renderer
	 *
	 * @param   string  $layoutId  Layout identifier
	 *
	 * @return  JLayout
	 *
	 * @since   3.5
	 */
	protected function getRenderer($layoutId = 'default')
	{
		$renderer = new JLayoutFile($layoutId);

		$renderer->setIncludePaths($this->getLayoutsPaths());

		return $renderer;
	}

	/**
	 * Get the data that will be sent to the stats server.
	 *
	 * @return  array.
	 *
	 * @since   3.5
	 */
	private function getStatsData()
	{
		return array(
			'unique_id'   => $this->getUniqueId(),
			'php_version' => PHP_VERSION,
			'db_type'     => $this->db->name,
			'db_version'  => $this->db->getVersion(),
			'cms_version' => JVERSION,
			'server_os'   => php_uname('s') . ' ' . php_uname('r')
		);
	}

	/**
	 * Get the unique id. Generates one if none is set.
	 *
	 * @return  integer
	 *
	 * @since   3.5
	 */
	private function getUniqueId()
	{
		if (null === $this->uniqueId)
		{
			$this->uniqueId = $this->params->get('unique_id', hash('sha1', JUserHelper::genRandomPassword(28) . time()));
		}

		return $this->uniqueId;
	}

	/**
	 * Check if current user is allowed to send the data
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isAllowedUser()
	{
		return JFactory::getUser()->authorise('core.admin');
	}

	/**
	 * Check if the debug is enabled
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isDebugEnabled()
	{
		return ((int) $this->params->get('debug', 0) === 1);
	}

	/**
	 * Check if last_run + interval > now
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isUpdateRequired()
	{
		$last     = (int) $this->params->get('lastrun', 0);
		$interval = (int) $this->params->get('interval', 12);
		$mode     = (int) $this->params->get('mode', 0);

		if ($mode === static::MODE_ALLOW_NEVER)
		{
			return false;
		}

		// Never updated or debug enabled
		if (!$last || !$interval || $this->isDebugEnabled())
		{
			return true;
		}

		return (abs(time() - $last) > $interval * 3600);
	}

	/**
	 * Check valid AJAX request
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isAjaxRequest()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Save the plugin parameters
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function saveParams()
	{
		// Update params
		$this->params->set('lastrun', time());
		$this->params->set('unique_id', $this->getUniqueId());
		$interval = (int) $this->params->get('interval', 12);
		$this->params->set('interval', $interval ? $interval : 12);

		$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__extensions'))
				->set($this->db->quoteName('params') . ' = ' . $this->db->quote($this->params->toString('JSON')))
				->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
				->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'))
				->where($this->db->quoteName('element') . ' = ' . $this->db->quote('stats'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$this->db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return false;
		}

		try
		{
			// Update the plugin parameters
			$result = $this->db->setQuery($query)->execute();

			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$this->db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$this->db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		return $result;
	}

	/**
	 * Send the stats to the stats server
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function sendStats()
	{
		try
		{
			// Don't let the request take longer than 2 seconds to avoid page timeout issues
			$response = JHttpFactory::getHttp()->post($this->params->get('url', 'https://developer.joomla.org/stats/submit'), $this->getStatsData(), null, 2);
		}
		catch (UnexpectedValueException $e)
		{
			// There was an error sending stats. Should we do anything?
			throw new RuntimeException('Could not send site statistics to remote server: ' . $e->getMessage(), 500);
		}
		catch (RuntimeException $e)
		{
			// There was an error connecting to the server or in the post request
			throw new RuntimeException('Could not connect to statistics server: ' . $e->getMessage(), 500);
		}
		catch (Exception $e)
		{
			// An unexpected error in processing; don't let this failure kill the site
			throw new RuntimeException('Unexpected error connecting to statistics server: ' . $e->getMessage(), 500);
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
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' :
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
