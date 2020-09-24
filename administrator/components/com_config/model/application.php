<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

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
	 * @return	array  An array containing all global config data.
	 *
	 * @since	1.6
	 */
	public function getData()
	{
		// Get the config data.
		$config = new JConfig;
		$data   = ArrayHelper::fromObject($config);

		// Get the correct driver at runtime
		$data['dbtype'] = JFactory::getDbo()->getName();

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params          = JComponentHelper::getParams('com_config');
		$data['filters'] = ArrayHelper::fromObject($params->get('filters'));

		// If no filter data found, get from com_content (update of 1.6/1.7 site)
		if (empty($data['filters']))
		{
			$contentParams = JComponentHelper::getParams('com_content');
			$data['filters'] = ArrayHelper::fromObject($contentParams->get('filters'));
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
		$dispatcher = JEventDispatcher::getInstance();

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
			JDatabaseDriver::getInstance($options)->getVersion();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT'), 'error');

			return false;
		}

		// Check if we can set the Force SSL option
		if ((int) $data['force_ssl'] !== 0 && (int) $data['force_ssl'] !== (int) JFactory::getConfig()->get('force_ssl', '0'))
		{
			try
			{
				// Make an HTTPS request to check if the site is available in HTTPS.
				$host    = JUri::getInstance()->getHost();
				$options = new \Joomla\Registry\Registry;
				$options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

				// Do not check for valid server certificate here, leave this to the user, moreover disable using a proxy if any is configured.
				$options->set('transport.curl',
					array(
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_PROXY => null,
						CURLOPT_PROXYUSERPWD => null,
					)
				);
				$response = JHttpFactory::getHttp($options)->get('https://' . $host . JUri::root(true) . '/', array('Host' => $host), 10);

				// If available in HTTPS check also the status code.
				if (!in_array($response->code, array(200, 503, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 401), true))
				{
					throw new RuntimeException(JText::_('COM_CONFIG_ERROR_SSL_NOT_AVAILABLE_HTTP_CODE'));
				}
			}
			catch (RuntimeException $e)
			{
				$data['force_ssl'] = 0;

				// Also update the user state
				$app->setUserState('com_config.config.global.data.force_ssl', 0);

				// Inform the user
				$app->enqueueMessage(JText::sprintf('COM_CONFIG_ERROR_SSL_NOT_AVAILABLE', $e->getMessage()), 'warning');
			}
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
					$app->enqueueMessage($asset->getError(), 'error');

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
			$registry = new Registry(array('filters' => $data['filters']));

			$extension = JTable::getInstance('extension');

			// Get extension_id
			$extensionId = $extension->find(array('name' => 'com_config'));

			if ($extension->load((int) $extensionId))
			{
				$extension->params = (string) $registry;

				if (!$extension->check() || !$extension->store())
				{
					$app->enqueueMessage($extension->getError(), 'error');

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
		$prev = ArrayHelper::fromObject($prev);

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

		// Set the shared session configuration
		if (isset($data['shared_session']))
		{
			$currentShared = isset($prev['shared_session']) ? $prev['shared_session'] : '0';

			// Has the user enabled shared sessions?
			if ($data['shared_session'] == 1 && $currentShared == 0)
			{
				// Generate a random shared session name
				$data['session_name'] = JUserHelper::genRandomPassword(16);
			}

			// Has the user disabled shared sessions?
			if ($data['shared_session'] == 0 && $currentShared == 1)
			{
				// Remove the session name value
				unset($data['session_name']);
			}
		}

		if (empty($data['cache_handler']))
		{
			$data['caching'] = 0;
		}

		/*
		 * Look for a custom cache_path
		 * First check if a path is given in the submitted data, then check if a path exists in the previous data, otherwise use the default
		 */
		if (!empty($data['cache_path']))
		{
			$path = $data['cache_path'];
		}
		elseif (!empty($prev['cache_path']))
		{
			$path = $prev['cache_path'];
		}
		else
		{
			$path = JPATH_SITE . '/cache';
		}

		// Give a warning if the cache-folder can not be opened
		if ($data['caching'] > 0 && $data['cache_handler'] == 'file' && @opendir($path) == false)
		{
			$error = true;

			// If a custom path is in use, try using the system default instead of disabling cache
			if ($path !== JPATH_SITE . '/cache' && @opendir(JPATH_SITE . '/cache') != false)
			{
				try
				{
					JLog::add(
						JText::sprintf('COM_CONFIG_ERROR_CUSTOM_CACHE_PATH_NOTWRITABLE_USING_DEFAULT', $path, JPATH_SITE . '/cache'),
						JLog::WARNING,
						'jerror'
					);
				}
				catch (RuntimeException $logException)
				{
					$app->enqueueMessage(
						JText::sprintf('COM_CONFIG_ERROR_CUSTOM_CACHE_PATH_NOTWRITABLE_USING_DEFAULT', $path, JPATH_SITE . '/cache'),
						'warning'
					);
				}

				$path  = JPATH_SITE . '/cache';
				$error = false;

				$data['cache_path'] = '';
			}

			if ($error)
			{
				try
				{
					JLog::add(JText::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), JLog::WARNING, 'jerror');
				}
				catch (RuntimeException $exception)
				{
					$app->enqueueMessage(JText::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), 'warning');
				}

				$data['caching'] = 0;
			}
		}

		// Did the user remove their custom cache path?  Don't save the variable to the config
		if (empty($data['cache_path']))
		{
			unset($data['cache_path']);
		}

		// Clean the cache if disabled but previously enabled or changing cache handlers; these operations use the `$prev` data already in memory
		if ((!$data['caching'] && $prev['caching']) || $data['cache_handler'] !== $prev['cache_handler'])
		{
			try
			{
				JFactory::getCache()->clean();
			}
			catch (JCacheExceptionConnecting $exception)
			{
				try
				{
					JLog::add(JText::_('COM_CONFIG_ERROR_CACHE_CONNECTION_FAILED'), JLog::WARNING, 'jerror');
				}
				catch (RuntimeException $logException)
				{
					$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CACHE_CONNECTION_FAILED'), 'warning');
				}
			}
			catch (JCacheExceptionUnsupported $exception)
			{
				try
				{
					JLog::add(JText::_('COM_CONFIG_ERROR_CACHE_DRIVER_UNSUPPORTED'), JLog::WARNING, 'jerror');
				}
				catch (RuntimeException $logException)
				{
					$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CACHE_DRIVER_UNSUPPORTED'), 'warning');
				}
			}
		}

		// Create the new configuration object.
		$config = new Registry($data);

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

		$result = $dispatcher->trigger('onApplicationBeforeSave', array($config));

		// Store the data.
		if (in_array(false, $result, true))
		{
			throw new RuntimeException(JText::_('COM_CONFIG_ERROR_UNKNOWN_BEFORE_SAVING'));
		}

		// Write the configuration file.
		$result = $this->writeConfigFile($config);

		// Trigger the after save event.
		$dispatcher->trigger('onApplicationAfterSave', array($config));

		return $result;
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
		$dispatcher = JEventDispatcher::getInstance();

		// Get the previous configuration.
		$prev = new JConfig;
		$prev = ArrayHelper::fromObject($prev);

		// Create the new configuration object, and unset the root_user property
		unset($prev['root_user']);
		$config = new Registry($prev);

		$result = $dispatcher->trigger('onApplicationBeforeSave', array($config));

		// Store the data.
		if (in_array(false, $result, true))
		{
			throw new RuntimeException(JText::_('COM_CONFIG_ERROR_UNKNOWN_BEFORE_SAVING'));
		}

		// Write the configuration file.
		$result = $this->writeConfigFile($config);

		// Trigger the after save event.
		$dispatcher->trigger('onApplicationAfterSave', array($config));

		return $result;
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

		// Invalidates the cached configuration file
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($file);
		}

		// Attempt to make the file unwriteable if NOT using FTP.
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
	 * @return  array  A list of result data.
	 *
	 * @since   3.5
	 */
	public function storePermissions($permission = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (is_null($permission))
		{
			// Get data from input.
			$permission = array(
				'component' => $app->input->get('comp'),
				'action'    => $app->input->get('action'),
				'rule'      => $app->input->get('rule'),
				'value'     => $app->input->get('value'),
				'title'     => $app->input->get('title', '', 'RAW')
			);
		}

		// We are creating a new item so we don't have an item id so don't allow.
		if (substr($permission['component'], -6) === '.false')
		{
			$app->enqueueMessage(JText::_('JLIB_RULES_SAVE_BEFORE_CHANGE_PERMISSIONS'), 'error');

			return false;
		}

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.admin', $permission['component']))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$permission['component'] = empty($permission['component']) ? 'root.1' : $permission['component'];

		// Current view is global config?
		$isGlobalConfig = $permission['component'] === 'root.1';

		// Check if changed group has Super User permissions.
		$isSuperUserGroupBefore = JAccess::checkGroup($permission['rule'], 'core.admin');

		// Check if current user belongs to changed group.
		$currentUserBelongsToGroup = in_array((int) $permission['rule'], $user->groups) ? true : false;

		// Get current user groups tree.
		$currentUserGroupsTree = JAccess::getGroupsByUser($user->id, true);

		// Check if current user belongs to changed group.
		$currentUserSuperUser = $user->authorise('core.admin');

		// If user is not Super User cannot change the permissions of a group it belongs to.
		if (!$currentUserSuperUser && $currentUserBelongsToGroup)
		{
			$app->enqueueMessage(JText::_('JLIB_USER_ERROR_CANNOT_CHANGE_OWN_GROUPS'), 'error');

			return false;
		}

		// If user is not Super User cannot change the permissions of a group it belongs to.
		if (!$currentUserSuperUser && in_array((int) $permission['rule'], $currentUserGroupsTree))
		{
			$app->enqueueMessage(JText::_('JLIB_USER_ERROR_CANNOT_CHANGE_OWN_PARENT_GROUPS'), 'error');

			return false;
		}

		// If user is not Super User cannot change the permissions of a Super User Group.
		if (!$currentUserSuperUser && $isSuperUserGroupBefore && !$currentUserBelongsToGroup)
		{
			$app->enqueueMessage(JText::_('JLIB_USER_ERROR_CANNOT_CHANGE_SUPER_USER'), 'error');

			return false;
		}

		// If user is not Super User cannot change the Super User permissions in any group it belongs to.
		if ($isSuperUserGroupBefore && $currentUserBelongsToGroup && $permission['action'] === 'core.admin')
		{
			$app->enqueueMessage(JText::_('JLIB_USER_ERROR_CANNOT_DEMOTE_SELF'), 'error');

			return false;
		}

		try
		{
			$asset  = JTable::getInstance('asset');
			$result = $asset->loadByName($permission['component']);

			if ($result === false)
			{
				$data = array($permission['action'] => array($permission['rule'] => $permission['value']));

				$rules        = new JAccessRules($data);
				$asset->rules = (string) $rules;
				$asset->name  = (string) $permission['component'];
				$asset->title = (string) $permission['title'];

				// Get the parent asset id so we have a correct tree.
				$parentAsset = JTable::getInstance('Asset');

				if (strpos($asset->name, '.') !== false)
				{
					$assetParts = explode('.', $asset->name);
					$parentAsset->loadByName($assetParts[0]);
					$parentAssetId = $parentAsset->id;
				}
				else
				{
					$parentAssetId = $parentAsset->getRootId();
				}

				/**
				 * @to do: incorrect ACL stored
				 * When changing a permission of an item that doesn't have a row in the asset table the row a new row is created.
				 * This works fine for item <-> component <-> global config scenario and component <-> global config scenario.
				 * But doesn't work properly for item <-> section(s) <-> component <-> global config scenario,
				 * because a wrong parent asset id (the component) is stored.
				 * Happens when there is no row in the asset table (ex: deleted or not created on update).
				 */

				$asset->setLocation($parentAssetId, 'last-child');
			}
			else
			{
				// Decode the rule settings.
				$temp = json_decode($asset->rules, true);

				// Check if a new value is to be set.
				if (isset($permission['value']))
				{
					// Check if we already have an action entry.
					if (!isset($temp[$permission['action']]))
					{
						$temp[$permission['action']] = array();
					}

					// Check if we already have a rule entry.
					if (!isset($temp[$permission['action']][$permission['rule']]))
					{
						$temp[$permission['action']][$permission['rule']] = array();
					}

					// Set the new permission.
					$temp[$permission['action']][$permission['rule']] = (int) $permission['value'];

					// Check if we have an inherited setting.
					if ($permission['value'] === '')
					{
						unset($temp[$permission['action']][$permission['rule']]);
					}

					// Check if we have any rules.
					if (!$temp[$permission['action']])
					{
						unset($temp[$permission['action']]);
					}
				}
				else
				{
					// There is no value so remove the action as it's not needed.
					unset($temp[$permission['action']]);
				}

				$asset->rules = json_encode($temp, JSON_FORCE_OBJECT);
			}

			if (!$asset->check() || !$asset->store())
			{
				$app->enqueueMessage(JText::_('JLIB_UNKNOWN'), 'error');

				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// All checks done.
		$result = array(
			'text'    => '',
			'class'   => '',
			'result'  => true,
		);

		// Show the current effective calculated permission considering current group, path and cascade.

		try
		{
			// Get the asset id by the name of the component.
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__assets'))
				->where($this->db->quoteName('name') . ' = ' . $this->db->quote($permission['component']));

			$this->db->setQuery($query);

			$assetId = (int) $this->db->loadResult();

			// Fetch the parent asset id.
			$parentAssetId = null;

			/**
			 * @to do: incorrect info
			 * When creating a new item (not saving) it uses the calculated permissions from the component (item <-> component <-> global config).
			 * But if we have a section too (item <-> section(s) <-> component <-> global config) this is not correct.
			 * Also, currently it uses the component permission, but should use the calculated permissions for achild of the component/section.
			 */

			// If not in global config we need the parent_id asset to calculate permissions.
			if (!$isGlobalConfig)
			{
				// In this case we need to get the component rules too.
				$query->clear()
					->select($this->db->quoteName('parent_id'))
					->from($this->db->quoteName('#__assets'))
					->where($this->db->quoteName('id') . ' = ' . $assetId);

				$this->db->setQuery($query);

				$parentAssetId = (int) $this->db->loadResult();
			}

			// Get the group parent id of the current group.
			$query->clear()
				->select($this->db->quoteName('parent_id'))
				->from($this->db->quoteName('#__usergroups'))
				->where($this->db->quoteName('id') . ' = ' . (int) $permission['rule']);

			$this->db->setQuery($query);

			$parentGroupId = (int) $this->db->loadResult();

			// Count the number of child groups of the current group.
			$query->clear()
				->select('COUNT(' . $this->db->quoteName('id') . ')')
				->from($this->db->quoteName('#__usergroups'))
				->where($this->db->quoteName('parent_id') . ' = ' . (int) $permission['rule']);

			$this->db->setQuery($query);

			$totalChildGroups = (int) $this->db->loadResult();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Clear access statistics.
		JAccess::clearStatics();

		// After current group permission is changed we need to check again if the group has Super User permissions.
		$isSuperUserGroupAfter = JAccess::checkGroup($permission['rule'], 'core.admin');

		// Get the rule for just this asset (non-recursive) and get the actual setting for the action for this group.
		$assetRule = JAccess::getAssetRules($assetId, false, false)->allow($permission['action'], $permission['rule']);

		// Get the group, group parent id, and group global config recursive calculated permission for the chosen action.
		$inheritedGroupRule = JAccess::checkGroup($permission['rule'], $permission['action'], $assetId);

		if (!empty($parentAssetId))
		{
			$inheritedGroupParentAssetRule = JAccess::checkGroup($permission['rule'], $permission['action'], $parentAssetId);
		}
		else
		{
			$inheritedGroupParentAssetRule = null;
		}

		$inheritedParentGroupRule = !empty($parentGroupId) ? JAccess::checkGroup($parentGroupId, $permission['action'], $assetId) : null;

		// Current group is a Super User group, so calculated setting is "Allowed (Super User)".
		if ($isSuperUserGroupAfter)
		{
			$result['class'] = 'label label-success';
			$result['text'] = '<span class="icon-lock icon-white" aria-hidden="true"></span>' . JText::_('JLIB_RULES_ALLOWED_ADMIN');
		}
		// Not super user.
		else
		{
			// First get the real recursive calculated setting and add (Inherited) to it.

			// If recursive calculated setting is "Denied" or null. Calculated permission is "Not Allowed (Inherited)".
			if ($inheritedGroupRule === null || $inheritedGroupRule === false)
			{
				$result['class'] = 'label label-important';
				$result['text']  = JText::_('JLIB_RULES_NOT_ALLOWED_INHERITED');
			}
			// If recursive calculated setting is "Allowed". Calculated permission is "Allowed (Inherited)".
			else
			{
				$result['class'] = 'label label-success';
				$result['text']  = JText::_('JLIB_RULES_ALLOWED_INHERITED');
			}

			// Second part: Overwrite the calculated permissions labels if there is an explicit permission in the current group.

			/**
			 * @todo: incorrect info
			 * If a component has a permission that doesn't exists in global config (ex: frontend editing in com_modules) by default
			 * we get "Not Allowed (Inherited)" when we should get "Not Allowed (Default)".
			 */

			// If there is an explicit permission "Not Allowed". Calculated permission is "Not Allowed".
			if ($assetRule === false)
			{
				$result['class'] = 'label label-important';
				$result['text']  = JText::_('JLIB_RULES_NOT_ALLOWED');
			}
			// If there is an explicit permission is "Allowed". Calculated permission is "Allowed".
			elseif ($assetRule === true)
			{
				$result['class'] = 'label label-success';
				$result['text']  = JText::_('JLIB_RULES_ALLOWED');
			}

			// Third part: Overwrite the calculated permissions labels for special cases.

			// Global configuration with "Not Set" permission. Calculated permission is "Not Allowed (Default)".
			if (empty($parentGroupId) && $isGlobalConfig === true && $assetRule === null)
			{
				$result['class'] = 'label label-important';
				$result['text']  = JText::_('JLIB_RULES_NOT_ALLOWED_DEFAULT');
			}

			/**
			 * Component/Item with explicit "Denied" permission at parent Asset (Category, Component or Global config) configuration.
			 * Or some parent group has an explicit "Denied".
			 * Calculated permission is "Not Allowed (Locked)".
			 */
			elseif ($inheritedGroupParentAssetRule === false || $inheritedParentGroupRule === false)
			{
				$result['class'] = 'label label-important';
				$result['text']  = '<span class="icon-lock icon-white" aria-hidden="true"></span>' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED');
			}
		}

		// If removed or added super user from group, we need to refresh the page to recalculate all settings.
		if ($isSuperUserGroupBefore != $isSuperUserGroupAfter)
		{
			$app->enqueueMessage(JText::_('JLIB_RULES_NOTICE_RECALCULATE_GROUP_PERMISSIONS'), 'notice');
		}

		// If this group has child groups, we need to refresh the page to recalculate the child settings.
		if ($totalChildGroups > 0)
		{
			$app->enqueueMessage(JText::_('JLIB_RULES_NOTICE_RECALCULATE_GROUP_CHILDS_PERMISSIONS'), 'notice');
		}

		return $result;
	}

	/**
	 * Method to send a test mail which is called via an AJAX request
	 *
	 * @return boolean
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

		$mail = JFactory::getMailer();

		// Prepare email and send try to send it
		$mailSubject = JText::sprintf('COM_CONFIG_SENDMAIL_SUBJECT', $app->get('sitename'));
		$mailBody    = JText::sprintf('COM_CONFIG_SENDMAIL_BODY', JText::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($mail->Mailer)));

		if ($mail->sendMail($app->get('mailfrom'), $app->get('fromname'), $app->get('mailfrom'), $mailSubject, $mailBody) === true)
		{
			$methodName = JText::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($mail->Mailer));

			// If JMail send the mail using PHP Mail as fallback.
			if ($mail->Mailer != $app->get('mailer'))
			{
				$app->enqueueMessage(JText::sprintf('COM_CONFIG_SENDMAIL_SUCCESS_FALLBACK', $app->get('mailfrom'), $methodName), 'warning');
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_CONFIG_SENDMAIL_SUCCESS', $app->get('mailfrom'), $methodName), 'message');
			}

			return true;
		}

		$app->enqueueMessage(JText::_('COM_CONFIG_SENDMAIL_ERROR'), 'error');

		return false;
	}
}
