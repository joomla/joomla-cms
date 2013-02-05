<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');

/**
 * Module installer
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 */
class JInstallerModule extends JAdapterInstance
{
	/**
	 * Install function routing
	 *
	 * @var    string
	 * @since 11.1
	 */
	protected $route = 'Install';

	/**
	 * @var
	 * @since 11.1
	 */
	protected $manifest = null;

	/**
	 * @var
	 * @since 11.1
	 */
	protected $manifest_script = null;

	/**
	 * Extension name
	 *
	 * @var
	 * @since   11.1
	 */
	protected $name = null;

	/**
	 * @var
	 * @since  11.1
	 */
	protected $element = null;

	/**
	 * @var    string
	 * @since 11.1
	 */
	protected $scriptElement = null;

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path where we find language files
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function loadLanguage($path = null)
	{
		$source = $this->parent->getPath('source');

		if (!$source)
		{
			$this->parent
				->setPath(
				'source',
				($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $this->parent->extension->element
			);
		}

		$this->manifest = $this->parent->getManifest();

		if ($this->manifest->files)
		{
			$element = $this->manifest->files;
			$extension = '';

			if (count($element->children()))
			{
				foreach ($element->children() as $file)
				{
					if ((string) $file->attributes()->module)
					{
						$extension = strtolower((string) $file->attributes()->module);
						break;
					}
				}
			}

			if ($extension)
			{
				$lang = JFactory::getLanguage();
				$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $extension;
				$folder = (string) $element->attributes()->folder;

				if ($folder && file_exists("$path/$folder"))
				{
					$source = "$path/$folder";
				}

				$client = (string) $this->manifest->attributes()->client;
				$lang->load($extension . '.sys', $source, null, false, false)
					|| $lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), null, false, false)
					|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
					|| $lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), $lang->getDefault(), false, false);
			}
		}
	}

	/**
	 * Custom install method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function install()
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		// Manifest Document Setup Section

		// Set the extensions name
		$name = (string) $this->manifest->name;
		$name = JFilterInput::getInstance()->clean($name, 'string');
		$this->set('name', $name);

		// Get the component description
		$description = (string) $this->manifest->description;
		if ($description)
		{
			$this->parent->set('message', JText::_($description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		// Target Application Section
		// Get the target application
		if ($cname = (string) $this->manifest->attributes()->client)
		{
			// Attempt to map the client to a base path
			$client = JApplicationHelper::getClientInfo($cname, true);

			if ($client === false)
			{
				$this->parent
					->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_UNKNOWN_CLIENT', JText::_('JLIB_INSTALLER_' . $this->route), $client->name));
				return false;
			}

			$basePath = $client->path;
			$clientId = $client->id;
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		// Set the installation path
		$element = '';
		if (count($this->manifest->files->children()))
		{
			foreach ($this->manifest->files->children() as $file)
			{
				if ((string) $file->attributes()->module)
				{
					$element = (string) $file->attributes()->module;
					$this->set('element', $element);

					break;
				}
			}
		}
		if (!empty($element))
		{
			$this->parent->setPath('extension_root', $basePath . '/modules/' . $element);
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_INSTALL_NOFILE', JText::_('JLIB_INSTALLER_' . $this->route)));

			return false;
		}

		// Check to see if a module by the same name is already installed
		// If it is, then update the table because if the files aren't there
		// we can assume that it was (badly) uninstalled
		// If it isn't, add an entry to extensions
		$query = $db->getQuery(true);
		$query->select($query->qn('extension_id'))->from($query->qn('#__extensions'));
		$query->where($query->qn('element') . ' = ' . $query->q($element))->where($query->qn('client_id') . ' = ' . (int) $clientId);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JException $e)
		{
			// Install failed, roll back changes
			$this->parent
				->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true)));

			return false;
		}

		$id = $db->loadResult();

		// If the module directory already exists, then we will assume that the
		// module is already installed or another module is using that
		// directory.
		// Check that this is either an issue where its not overwriting or it is
		// set to upgrade anyway

		if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->isOverwrite() || $this->parent->isUpgrade()))
		{
			// Look for an update function or update tag
			$updateElement = $this->manifest->update;
			// Upgrade manually set or
			// Update function available or
			// Update tag detected
			if ($this->parent->isUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
				|| $updateElement)
			{
				// Force this one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);

				if ($id)
				{
					// If there is a matching extension mark this as an update; semantics really
					$this->route = 'Update';
				}
			}
			elseif (!$this->parent->isOverwrite())
			{
				// Overwrite is set
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_INSTALL_DIRECTORY', JText::_('JLIB_INSTALLER_' . $this->route),
						$this->parent->getPath('extension_root')
					)
				);

				return false;
			}
		}

		// Installer Trigger Loading

		// If there is an manifest class file, let's load it; we'll copy it later (don't have destination yet)
		$this->scriptElement = $this->manifest->scriptfile;
		$manifestScript = (string) $this->manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}

			// Set the class name
			$classname = $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance.
				$this->parent->manifestClass = new $classname($this);
				// And set this so we can copy it later.
				$this->set('manifest_script', $manifestScript);

				// Note: if we don't find the class, don't bother to copy the file.
			}
		}

		// Run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight($this->route, $this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here
		$msg = ob_get_contents();
		ob_end_clean();

		// Filesystem Processing Section

		// If the module directory does not exist, lets create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_root')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_root')))
			{
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_INSTALL_CREATE_DIRECTORY', JText::_('JLIB_INSTALLER_' . $this->route),
						$this->parent->getPath('extension_root')
					)
				);

				return false;
			}
		}

		// Since we created the module directory and will want to remove it if
		// we have to roll back the installation, let's add it to the
		// installation step stack

		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all necessary files
		if ($this->parent->parseFiles($this->manifest->files, -1) === false)
		{
			// Install failed, roll back changes
			$this->parent->abort();

			return false;
		}

		// If there is a manifest script, let's copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source') . '/' . $this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_MANIFEST'));

					return false;
				}
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->media, $clientId);
		$this->parent->parseLanguages($this->manifest->languages, $clientId);

		// Parse deprecated tags
		$this->parent->parseFiles($this->manifest->images, -1);

		// Database Processing Section

		$row = JTable::getInstance('extension');

		// Was there a module already installed with the same name?
		if ($id)
		{
			// Load the entry and update the manifest_cache
			$row->load($id);
			$row->name = $this->get('name'); // update name
			$row->manifest_cache = $this->parent->generateManifestCache(); // update manifest

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent
					->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true)));

				return false;
			}
		}
		else
		{
			$row->set('name', $this->get('name'));
			$row->set('type', 'module');
			$row->set('element', $this->get('element'));
			$row->set('folder', ''); // There is no folder for modules
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', $clientId == 1 ? 2 : 0);
			$row->set('client_id', $clientId);
			$row->set('params', $this->parent->getParams());
			$row->set('custom_data', ''); // custom data
			$row->set('manifest_cache', $this->parent->generateManifestCache());

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent
					->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true)));
				return false;
			}

			// Set the insert id
			$row->extension_id = $db->insertid();

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array('type' => 'extension', 'extension_id' => $row->extension_id));

			// Create unpublished module in jos_modules
			$name = preg_replace('#[\*?]#', '', JText::_($this->get('name')));
			$module = JTable::getInstance('module');
			$module->set('title', $name);
			$module->set('module', $this->get('element'));
			$module->set('access', '1');
			$module->set('showtitle', '1');
			$module->set('client_id', $clientId);
			$module->set('language', '*');

			$module->store();
		}

		// Let's run the queries for the module
		// If Joomla 1.5 compatible, with discrete sql files, execute appropriate
		// file for utf-8 support or non-utf-8 support

		// Try for Joomla 1.5 type queries
		// Second argument is the utf compatible version attribute
		if (strtolower($this->route) == 'install')
		{
			$utfresult = $this->parent->parseSQLFiles($this->manifest->install->sql);

			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$this->parent
					->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_MOD_INSTALL_SQL_ERROR', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
				);

				return false;
			}

			// Set the schema version to be the latest update version
			if ($this->manifest->update)
			{
				$this->parent->setSchemaVersion($this->manifest->update->schemas, $row->extension_id);
			}
		}
		elseif (strtolower($this->route) == 'update')
		{
			if ($this->manifest->update)
			{
				$result = $this->parent->parseSchemaUpdates($this->manifest->update->schemas, $row->extension_id);
				if ($result === false)
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_MOD_UPDATE_SQL_ERROR', $db->stderr(true)));
					return false;
				}
			}
		}

		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $this->route))
		{
			if ($this->parent->manifestClass->{$this->route}($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		// Finalization and Cleanup Section

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_COPY_SETUP'));

			return false;
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight($this->route, $this);
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		return $row->get('extension_id');
	}

	/**
	 * Custom update method
	 *
	 * This is really a shell for the install system
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function update()
	{
		// Set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);
		// Set the route for the install
		$this->route = 'Update';

		// Go to install which handles updates properly
		return $this->install();
	}

	/**
	 * Custom discover method
	 *
	 * @return  array  JExtension list of extensions available
	 *
	 * @since   11.1
	 */
	public function discover()
	{
		$results = array();
		$site_list = JFolder::folders(JPATH_SITE . '/modules');
		$admin_list = JFolder::folders(JPATH_ADMINISTRATOR . '/modules');
		$site_info = JApplicationHelper::getClientInfo('site', true);
		$admin_info = JApplicationHelper::getClientInfo('administrator', true);

		foreach ($site_list as $module)
		{
			$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . "/modules/$module/$module.xml");
			$extension = JTable::getInstance('extension');
			$extension->set('type', 'module');
			$extension->set('client_id', $site_info->id);
			$extension->set('element', $module);
			$extension->set('name', $module);
			$extension->set('state', -1);
			$extension->set('manifest_cache', json_encode($manifest_details));
			$results[] = clone $extension;
		}

		foreach ($admin_list as $module)
		{
			$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR . "/modules/$module/$module.xml");
			$extension = JTable::getInstance('extension');
			$extension->set('type', 'module');
			$extension->set('client_id', $admin_info->id);
			$extension->set('element', $module);
			$extension->set('name', $module);
			$extension->set('state', -1);
			$extension->set('manifest_cache', json_encode($manifest_details));
			$results[] = clone $extension;
		}

		return $results;
	}

	/**
	 * Custom discover_install method
	 *
	 * @return  mixed  Extension ID on success, boolean false on failure
	 *
	 * @since   11.1
	 */
	public function discover_install()
	{
		// Modules are like templates, and are one of the easiest
		// If its not in the extensions table we just add it
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/modules/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$description = (string) $this->parent->manifest->description;

		if ($description)
		{
			$this->parent->set('message', JText::_($description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		// TODO: Re-evaluate this; should we run installation triggers? postflight perhaps?
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		$this->parent->extension->params = $this->parent->getParams();
		if ($this->parent->extension->store())
		{
			return $this->parent->extension->get('extension_id');
		}
		else
		{
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_MOD_DISCOVER_STORE_DETAILS'));

			return false;
		}
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure.
	 *
	 * @since   11.1
	 */
	public function refreshManifestCache()
	{
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/modules/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		if ($this->parent->extension->store())
		{
			return true;
		}
		else
		{
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_MOD_REFRESH_MANIFEST_CACHE'));

			return false;
		}
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The id of the module to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$row = null;
		$retval = true;
		$db = $this->parent->getDbo();

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the module we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_WARNCOREMODULE', $row->name));
			return false;
		}

		// Get the extension root path
		$element = $row->element;
		$client = JApplicationHelper::getClientInfo($row->client_id);

		if ($client === false)
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_UNKNOWN_CLIENT', $row->client_id));
			return false;
		}
		$this->parent->setPath('extension_root', $client->path . '/modules/' . $element);

		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// Get the package manifest objecct
		// We do findManifest to avoid problem when uninstalling a list of extensions: getManifest cache its manifest file.
		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();

		// Attempt to load the language file; might have uninstall strings
		$this->loadLanguage(($row->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $element);

		// If there is an manifest class file, let's load it
		$this->scriptElement = $this->manifest->scriptfile;
		$manifestScript = (string) $this->manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('extension_root') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}

			// Set the class name
			$classname = $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);
				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);

				// Note: if we don't find the class, don't bother to copy the file
			}
		}

		ob_start();
		ob_implicit_flush(false);

		// Run uninstall if possible
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'uninstall'))
		{
			$this->parent->manifestClass->uninstall($this);
		}

		$msg = ob_get_contents();
		ob_end_clean();

		if (!($this->manifest instanceof SimpleXMLElement))
		{
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));

			return false;
		}

		/*
		 * Let's run the uninstall queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		// Try for Joomla 1.5 type queries
		// Second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

		if ($utfresult === false)
		{
			// Install failed, rollback changes
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_SQL_ERROR', $db->stderr(true)));
			$retval = false;
		}

		// Remove the schema version
		$query = $db->getQuery(true);
		$query->delete()->from('#__schemas')->where('extension_id = ' . $row->extension_id);
		$db->setQuery($query);
		$db->execute();

		// Remove other files
		$this->parent->removeFiles($this->manifest->media);
		$this->parent->removeFiles($this->manifest->languages, $row->client_id);

		// Let's delete all the module copies for the type we are uninstalling
		$query = $db->getQuery(true);
		$query->select($query->qn('id'))->from($query->qn('#__modules'));
		$query->where($query->qn('module') . ' = ' . $query->q($row->element));
		$query->where($query->qn('client_id') . ' = ' . (int) $row->client_id);
		$db->setQuery($query);

		try
		{
			$modules = $db->loadColumn();
		}
		catch (JException $e)
		{
			$modules = array();
		}

		// Do we have any module copies?
		if (count($modules))
		{
			// Ensure the list is sane
			JArrayHelper::toInteger($modules);
			$modID = implode(',', $modules);

			// Wipe out any items assigned to menus
			$query = 'DELETE' . ' FROM #__modules_menu' . ' WHERE moduleid IN (' . $modID . ')';
			$db->setQuery($query);
			try
			{
				$db->execute();
			}
			catch (JException $e)
			{
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
				$retval = false;
			}

			// Wipe out any instances in the modules table
			$query = 'DELETE' . ' FROM #__modules' . ' WHERE id IN (' . $modID . ')';
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (JException $e)
			{
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
				$retval = false;
			}
		}

		// Now we will no longer need the module object, so let's delete it and free up memory
		$row->delete($row->extension_id);
		$query = 'DELETE FROM #__modules WHERE module = ' . $db->Quote($row->element) . ' AND client_id = ' . $row->client_id;
		$db->setQuery($query);

		try
		{
			// Clean up any other ones that might exist as well
			$db->execute();
		}
		catch (JException $e)
		{
			// Ignore the error...
		}

		unset($row);

		// Remove the installation folder
		if (!JFolder::delete($this->parent->getPath('extension_root')))
		{
			// JFolder should raise an error
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Custom rollback method
	 * - Roll back the menu item
	 *
	 * @param   array  $arg  Installation step to rollback
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	protected function _rollback_menu($arg)
	{
		// Get database connector object
		$db = $this->parent->getDbo();

		// Remove the entry from the #__modules_menu table
		$query = 'DELETE' . ' FROM #__modules_menu' . ' WHERE moduleid=' . (int) $arg['id'];
		$db->setQuery($query);

		try
		{
			return $db->execute();
		}
		catch (JException $e)
		{
			return false;
		}
	}

	/**
	 * Custom rollback method
	 * - Roll back the module item
	 *
	 * @param   array  $arg  Installation step to rollback
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	protected function _rollback_module($arg)
	{
		// Get database connector object
		$db = $this->parent->getDbo();

		// Remove the entry from the #__modules table
		$query = 'DELETE' . ' FROM #__modules' . ' WHERE id=' . (int) $arg['id'];
		$db->setQuery($query);
		try
		{
			return $db->execute();
		}
		catch (JException $e)
		{
			return false;
		}
	}
}
