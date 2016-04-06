<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Model for the global configuration
 *
 * @since  3.2
 */
class ConfigModelApplication extends ConfigModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return	array  An array containg all global config data.
	 *
	 * @since	1.6
	 */
	public function getData()
	{
		// Get the config data.
		$config = new JConfig;
		$data   = JArrayHelper::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params          = JComponentHelper::getParams('com_config');
		$data['filters'] = JArrayHelper::fromObject($params->get('filters'));

		// If no filter data found, get from com_content (update of 1.6/1.7 site)
		if (empty($data['filters']))
		{
			$contentParams = JComponentHelper::getParams('com_content');
			$data['filters'] = JArrayHelper::fromObject($contentParams->get('filters'));
		}

		// Check for data in the session.
		$temp = JFactory::getApplication()->getUserState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Check that we aren't setting wrong database configuration
		$options = array(
			'driver'   => $data['dbtype'],
			'host'     => $data['host'],
			'user'     => $data['user'],
			'password' => JFactory::getConfig()->get('password'),
			'database' => $data['db'],
			'prefix'   => $data['dbprefix']
		);

		try
		{
			$dbc = JDatabaseDriver::getInstance($options)->getVersion();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT'), 'error');

			return false;
		}

		// Save the rules
		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);

			// Check that we aren't removing our Super User permission
			// Need to get groups from database, since they might have changed
			$myGroups      = JAccess::getGroupsByUser(JFactory::getUser()->get('id'));
			$myRules       = $rules->getData();
			$hasSuperAdmin = $myRules['core.admin']->allow($myGroups);

			if (!$hasSuperAdmin)
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'), 'error');

				return false;
			}

			$asset = JTable::getInstance('asset');

			if ($asset->loadByName('root.1'))
			{
				$asset->rules = (string) $rules;

				if (!$asset->check() || !$asset->store())
				{
					$app->enqueueMessage(JText::_('SOME_ERROR_CODE'), 'error');

					return;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'), 'error');

				return false;
			}

			unset($data['rules']);
		}

		// Save the text filters
		if (isset($data['filters']))
		{
			$registry = new Registry;
			$registry->loadArray(array('filters' => $data['filters']));

			$extension = JTable::getInstance('extension');

			// Get extension_id
			$extension_id = $extension->find(array('name' => 'com_config'));

			if ($extension->load((int) $extension_id))
			{
				$extension->params = (string) $registry;

				if (!$extension->check() || !$extension->store())
				{
					$app->enqueueMessage(JText::_('SOME_ERROR_CODE'), 'error');

					return;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'), 'error');

				return false;
			}

			unset($data['filters']);
		}

		// Get the previous configuration.
		$prev = new JConfig;
		$prev = JArrayHelper::fromObject($prev);

		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);

		/*
		 * Perform miscellaneous options based on configuration settings/changes.
		 */

		// Escape the offline message if present.
		if (isset($data['offline_message']))
		{
			$data['offline_message'] = JFilterOutput::ampReplace($data['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
		}

		if (empty($data['cache_handler']))
		{
			$data['caching'] = 0;
		}

		$path = JPATH_SITE . '/cache';

		// Give a warning if the cache-folder can not be opened
		if ($data['caching'] > 0 && $data['cache_handler'] == 'file' && @opendir($path) == false)
		{
			JLog::add(JText::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), JLog::WARNING, 'jerror');
			$data['caching'] = 0;
		}

		// Clean the cache if disabled but previously enabled.
		if (!$data['caching'] && $prev['caching'])
		{
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Create the new configuration object.
		$config = new Registry('config');
		$config->loadArray($data);

		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('ftp_enable', $data['ftp_enable']);
		$temp->set('ftp_host', $data['ftp_host']);
		$temp->set('ftp_port', $data['ftp_port']);
		$temp->set('ftp_user', $data['ftp_user']);
		$temp->set('ftp_pass', $data['ftp_pass']);
		$temp->set('ftp_root', $data['ftp_root']);

		// Clear cache of com_config component.
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function removeroot()
	{
		// Get the previous configuration.
		$prev = new JConfig;
		$prev = JArrayHelper::fromObject($prev);

		// Create the new configuration object, and unset the root_user property
		$config = new Registry('config');
		unset($prev['root_user']);
		$config->loadArray($prev);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   Registry  $config  A Registry object containing all global config data.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	2.5.4
	 * @throws  RuntimeException
	 */
	private function writeConfigFile(Registry $config)
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		$app = JFactory::getApplication();

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		if (!JFile::write($file, $configuration))
		{
			throw new RuntimeException(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
		}

		// Attempt to make the file unwriteable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
		}

		return true;
	}

	/**
	 * Method to store the permission values in the asset table.
	 *
	 * This method will get an array with permission key value pairs and transform it
	 * into json and update the asset table in the database.
	 *
	 * @param   string  $permission  Need an array with Permissions (component, rule, value and title)
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   3.5
	 */
	public function storePermissions($permission)
	{
		try
		{
			// Load the current settings for this component
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName(array('name', 'rules')))
				->from($this->db->quoteName('#__assets'))
				->where($this->db->quoteName('name') . ' = ' . $this->db->quote($permission['component']));

			$this->db->setQuery($query);

			// Load the results as a list of stdClass objects (see later for more options on retrieving data).
			$results = $this->db->loadAssocList();

			if (empty($results))
			{
				$data = array();
				$data[$permission['action']] = array();
				$data[$permission['action']] = array($permission['rule'] => $permission['value']);

				$rules = new JAccessRules($data);
				$asset = JTable::getInstance('asset');
				$asset->rules = (string) $rules;
				$asset->name  = (string) $permission['component'];
				$asset->title = (string) $permission['title'];

				if (!$asset->check() || !$asset->store())
				{
					JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_CODE'), 'error');

					return false;
				}

				return true;
			}
			else
			{
				// Decode the rule settings
				$temp = json_decode($results[0]['rules'], true);

				// Check if a new value is to be set
				if (isset($permission['value']))
				{
					// Check if we already have an action entry
					if (!isset($temp[$permission['action']]))
					{
						$temp[$permission['action']] = array();
					}

					// Check if we already have a rule entry
					if (!isset($temp[$permission['action']][$permission['rule']]))
					{
						$temp[$permission['action']][$permission['rule']] = array();
					}

					// Set the new permission
					$temp[$permission['action']][$permission['rule']] = intval($permission['value']);

					// Check if we have an inherited setting
					if (strlen($permission['value']) == 0)
					{
						unset($temp[$permission['action']][$permission['rule']]);
					}
				}
				else
				{
					// There is no value so remove the action as it's not needed
					unset($temp[$permission['action']]);
				}

				// Store the new permissions
				$temp  = json_encode($temp);
				$query = $this->db->getQuery(true)
					->update($this->db->quoteName('#__assets'))
					->set('rules = ' . $this->db->quote($temp))
					->where($this->db->quoteName('name') . ' = ' . $this->db->quote($permission['component']));

				$this->db->setQuery($query);

				$result = $this->db->execute();

				return (bool) $result;
			}
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Method to send a test mail which is called via an AJAX request
	 *
	 * @return bool
	 *
	 * @since   3.5
	 * @throws Exception
	 */
	public function sendTestMail()
	{
		// Set the new values to test with the current settings
		$app = JFactory::getApplication();
		$input = $app->input;

		$app->set('smtpauth', $input->get('smtpauth'));
		$app->set('smtpuser', $input->get('smtpuser', '', 'STRING'));
		$app->set('smtppass', $input->get('smtppass', '', 'RAW'));
		$app->set('smtphost', $input->get('smtphost'));
		$app->set('smtpsecure', $input->get('smtpsecure'));
		$app->set('smtpport', $input->get('smtpport'));
		$app->set('mailfrom', $input->get('mailfrom', '', 'STRING'));
		$app->set('fromname', $input->get('fromname', '', 'STRING'));
		$app->set('mailer', $input->get('mailer'));
		$app->set('mailonline', $input->get('mailonline'));

		// Prepare email and send try to send it
		$mailSubject = JText::sprintf('COM_CONFIG_SENDMAIL_SUBJECT', $app->get('sitename'));
		$mailBody    = JText::sprintf('COM_CONFIG_SENDMAIL_BODY', JText::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($app->get('mailer'))));

		if (JFactory::getMailer()->sendMail($app->get('mailfrom'), $app->get('fromname'), $app->get('mailfrom'), $mailSubject, $mailBody) === true)
		{
			$methodName = JText::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($app->get('mailer')));
			$app->enqueueMessage(JText::sprintf('COM_CONFIG_SENDMAIL_SUCCESS', $app->get('mailfrom'), $methodName), 'success');

			return true;
		}

		$app->enqueueMessage(JText::_('COM_CONFIG_SENDMAIL_ERROR'), 'error');

		return false;
	}
}
