<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	System.session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

class plgSystemSession extends JPlugin
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
	 * This event is triggered after the framework has loaded and the
	 * application initialise method has been called.
	 *
	 * @access	public
	 * @return	void
	 */
	public function onAfterInitialise()
	{
		// Check if the required counterpart plugin is available and enabled.
		if (JPluginHelper::isEnabled('user', 'session'))
		{
			$session         = JFactory::getSession();
			$session_id      = $session->getId();
			$session_handler = $this->app->getCfg('session_handler');

			// Read the update flag name to check for.
			$plg_params      = new JRegistry(JPluginHelper::getPlugin('user', 'session')->params);
			$flag            = $plg_params->get('session_update_flag_name', 'refresh');

			if (in_array($session_handler, array('database','xcache')) && $session->get("session.{$flag}", null) == true)
			{
				$user = JFactory::getUser();
				$user->groups = JUserHelper::getUserGroups($user->id);
				$user->getAuthorisedGroups();
				$user->getAuthorisedViewLevels();

				// Load session data by id.
				$handler = JSessionStorage::getInstance($session_handler);

				if ($db_session = $handler->read($session_id))
				{
					// Get the session data.
					$db_session        = JSessionHelper::unserialize($db_session);

					// Populate helper vars.
					$sess_namespace    = current(array_keys($db_session));
					$sess_data         = current(array_values($db_session));

					// Replace session user data by updated logged in user data to.
					$sess_data['user'] = &$user;

					// Store updated session data.
					if (false === ($written = $handler->write($session_id, $sess_namespace .'|'. serialize($sess_data))))
					{
						throw new RuntimeException(JText::_('JLIB_SESSION_ERROR_UNSUPPORTED_HANDLER'), 500);
					}

				}

				// Unset refresh-flag.
				$session->set('session.refresh', null);

			}

		}

	}

}
