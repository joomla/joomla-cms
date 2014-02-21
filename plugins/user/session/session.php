<?php
/**
 * @package	Joomla.Plugin
 * @subpackage	User.session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

class plgUserSession extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var	boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

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
	 * Constructor.
	 *
	 * @param	object	$subject	The object to observe.
	 * @param	array	$config	An array that holds the plugin configuration.
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Pre-store-process the user data.
	 *
	 * @access	public
	 * @param	array	$old	The user data as they were before change
	 * @param	boolean	$isNew	Flag indicating whether the data relates to a new user instance
	 * @param	array	$new	The changed user data
	 * @throws	InvalidArgumentException
	 * @return	boolean
	 */
	public function onUserBeforeSave($old, $isNew, $new)
	{
		if ($this->app->isAdmin())
		{
			// Check if the required counterpart plugin is available and enabled.
			if (! JPluginHelper::isEnabled('system', 'session'))
			{
				return true;
			}

			// Dump the processed user's group data for comparison after saving done.
			$oldUserData = JFactory::getUser(0);
			$oldUserData->setProperties($old);

			$this->oldUserGroups = $oldUserData->get('groups');

		}

		return true;
	}

	/**
	 * Pre-store-process the user data.
	 *
	 * @access	public
	 * @param	array	$data	The saved user data
	 * @param	boolean	$isNew	Flag indicating whether the data relates to a new user instance
	 * @param	boolean	$result	The table store result
	 * @param	Exception	$error	The error that might have occurred while the table data was stored
	 * @return boolean
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		if ($this->app->isAdmin())
		{
			// Check if the required counterpart plugin is available and enabled.
			if (! JPluginHelper::isEnabled('system', 'session'))
			{
				return true;
			}

			$debug              = JDEBUG;
			$session_handler    = $this->app->getCfg('session_handler');
			$supported_handlers = array(
				'database',
// 				'memcache',
// 				'memcached',
				'xcache'
			);

			// Our tweak currently works for selected session storage handlers only.
			if (in_array($session_handler, $supported_handlers))
			{
				// Check if the processed user's group data has change.
				$newUserData = JFactory::getUser(0);
				$newUserData->setProperties($data);

				$newUserGroups = $newUserData->get('groups');
				$oldUserGroups = &$this->oldUserGroups;

				$hash_old = hash('md5', serialize(array_values($oldUserGroups)));
				$hash_new = hash('md5', serialize(array_values($newUserGroups)));

				// If so, set a flag into the user's session to trigger its session getting updated asap.
				if ($hash_old !== $hash_new)
				{
					// Get id of the user's session.
					$session_id = $this->db->setQuery(
						$this->db->getQuery(true)
						->from($this->db->qn('#__session'))
						->select($this->db->qn('session_id'))
						->where($this->db->qn('userid') . ' = ' . (int) $newUserData->get('id'))
					)
					->loadResult();

					if ($session_id)
					{
						// Get session handler.
						$handler = JSessionStorage::getInstance($session_handler);

						// Get name of the update flag to use.
						$flag    = $this->params->get('session_update_flag_name', 'refresh');

						// Load session data by id.
						if ($session = $handler->read($session_id))
						{
							// Unserialize session data.
							$session        = JSessionHelper::unserialize($session);

							// Populate helper vars.
							$sess_namespace = current(array_keys($session));
							$sess_data      = current(array_values($session));

							// Set refresh-flag.
							$sess_data["session.{$flag}"] = true;

							// Store updated session data.
							if (false === ($written = $handler->write($session_id, $sess_namespace .'|'. serialize($sess_data))))
							{
								$this->app->enqueueMessage(JText::_('PLG_USER_SESSION_ERROR_STORE_FAIL'), 'error');
							}

							if ($debug)
							{
								$this->app->enqueueMessage(JText::sprintf('PLG_USER_SESSION_ERROR_STORE_SUCCESS_DEBUG', ucfirst($session_handler)), 'notice');
							}

						}
						else
						{
							if ($debug)
							{
								$this->app->enqueueMessage(JText::sprintf('PLG_USER_SESSION_ERROR_STORE_FAIL_DEBUG', ucfirst($session_handler)), 'error');
							}

						}

					}

				}

			}
			else
			{
				// State the incompatibility so admins might consider to change the selected session handler.
				if ($debug)
				{
					$this->app->enqueueMessage(JText::sprintf('PLG_USER_SESSION_ERROR_UNSUPPORTED_HANDLER'), 'warning');
				}

			}

			// Delete dumped data.
			unset($this->oldUserGroups);

		}

		return true;
	}

}
