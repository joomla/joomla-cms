<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');
jimport('joomla.filesystem.folder');

/**
 * Plugin installer
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerAdapterPlugin extends JAdapterInstance
{
	/**
	 * Install function routing
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $route = 'install';

	/**
	 * The installation manifest XML object
	 *
	 * @var    SimpleXMLElement
	 * @since  3.1
	 */
	protected $manifest = null;

	/**
	 * A path to the PHP file that the scriptfile declaration in
	 * the manifest refers to.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $manifest_script = null;

	/**
	 * Name of the extension
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $name = null;

	/**
	 * <scriptfile> element of the extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $scriptElement = null;

	/**
	 * <files> element of the old extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $oldFiles = null;

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path where to find language files.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path = null)
	{
		$source = $this->parent->getPath('source');

		if (!$source)
		{
			$this->parent->setPath('source', JPATH_PLUGINS . '/' . $this->parent->extension->folder . '/' . $this->parent->extension->element);
		}
		$this->manifest = $this->parent->getManifest();
		$element = $this->manifest->files;

		if ($element)
		{
			$group = strtolower((string) $this->manifest->attributes()->group);
			$name = '';

			if (count($element->children()))
			{
				foreach ($element->children() as $file)
				{
					if ((string) $file->attributes()->plugin)
					{
						$name = strtolower((string) $file->attributes()->plugin);
						break;
					}
				}
			}
			if ($name)
			{
				$extension = "plg_${group}_${name}";
				$lang = JFactory::getLanguage();
				$source = $path ? $path : JPATH_PLUGINS . "/$group/$name";
				$folder = (string) $element->attributes()->folder;

				if ($folder && file_exists("$path/$folder"))
				{
					$source = "$path/$folder";
				}
				$lang->load($extension . '.sys', $source, null, false, false)
					|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
					|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
					|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
			}
		}
	}

	/**
	 * Custom install method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function install()
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		$xml = $this->manifest;

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extension name
		$name = (string) $xml->name;
		$name = JFilterInput::getInstance()->clean($name, 'string');
		$this->set('name', $name);

		// Get the plugin description
		$description = (string) $xml->description;

		if ($description)
		{
			$this->parent->set('message', JText::_($description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		/*
		 * Backward Compatibility
		 * @todo Deprecate in future version
		 */
		$type = (string) $xml->attributes()->type;

		// Set the installation path
		if (count($xml->files->children()))
		{
			foreach ($xml->files->children() as $file)
			{
				if ((string) $file->attributes()->$type)
				{
					$element = (string) $file->attributes()->$type;
					break;
				}
			}
		}
		$group = (string) $xml->attributes()->group;

		if (!empty($element) && !empty($group))
		{
			$this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $group . '/' . $element);
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_NO_FILE', JText::_('JLIB_INSTALLER_' . $this->route)));

			return false;
		}

		// Check if we should enable overwrite settings

		// Check to see if a plugin by the same name is already installed.
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote($group))
			->where($db->quoteName('element') . ' = ' . $db->quote($element));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent
				->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true)));

			return false;
		}
		$id = $db->loadResult();

		// If it's on the fs...
		if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->isOverwrite() || $this->parent->isUpgrade()))
		{
			$updateElement = $xml->update;

			// Upgrade manually set or update function available or update tag detected
			if ($this->parent->isUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
				|| $updateElement)
			{
				// Force this one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);

				if ($id)
				{
					// If there is a matching extension mark this as an update; semantics really
					$this->route = 'update';
				}
			}
			elseif (!$this->parent->isOverwrite())
			{
				// Overwrite is set
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_DIRECTORY', JText::_('JLIB_INSTALLER_' . $this->route),
						$this->parent->getPath('extension_root')
					)
				);

				return false;
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		// If there is an manifest class file, let's load it; we'll copy it later (don't have destination yet).
		if ((string) $xml->scriptfile)
		{
			$manifestScript = (string) $xml->scriptfile;
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}
			// If a dash is present in the group name, remove it
			$groupClass = str_replace('-', '', $group);

			// Set the class name
			$classname = 'plg' . $groupClass . $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
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
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_PLG_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here
		$msg = ob_get_contents();
		ob_end_clean();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// If the plugin directory does not exist, lets create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_root')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_root')))
			{
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_CREATE_DIRECTORY', JText::_('JLIB_INSTALLER_' . $this->route),
						$this->parent->getPath('extension_root')
					)
				);

				return false;
			}
		}

		// If we're updating at this point when there is always going to be an extension_root find the old XML files
		if ($this->route == 'update')
		{
			// Hunt for the original XML file
			$old_manifest = null;

			// Create a new installer because findManifest sets stuff; side effects!
			$tmpInstaller = new JInstaller;

			// Look in the extension root
			$tmpInstaller->setPath('source', $this->parent->getPath('extension_root'));

			if ($tmpInstaller->findManifest())
			{
				$old_manifest = $tmpInstaller->getManifest();
				$this->oldFiles = $old_manifest->files;
			}
		}

		/*
		 * If we created the plugin directory and will want to remove it if we
		 * have to roll back the installation, let's add it to the installation
		 * step stack
		 */

		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all necessary files
		if ($this->parent->parseFiles($xml->files, -1, $this->oldFiles) === false)
		{
			// Install failed, roll back changes
			$this->parent->abort();

			return false;
		}

		// Parse optional tags -- media and language files for plugins go in admin app
		$this->parent->parseMedia($xml->media, 1);
		$this->parent->parseLanguages($xml->languages, 1);

		// If there is a manifest script, lets copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source') . '/' . $this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->get('manifest_script');

			if (!file_exists($path['dest']))
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					$this->parent
						->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_MANIFEST', JText::_('JLIB_INSTALLER_' . $this->route)));

					return false;
				}
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		$row = JTable::getInstance('extension');

		// Was there a plugin with the same name already installed?
		if ($id)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_ALLREADY_EXISTS', JText::_('JLIB_INSTALLER_' . $this->route),
						$this->get('name')
					)
				);

				return false;
			}
			$row->load($id);
			$row->name = $this->get('name');
			$row->manifest_cache = $this->parent->generateManifestCache();

			// Update the manifest cache and name
			$row->store();
		}
		else
		{
			// Store in the extensions table (1.6)
			$row->name = $this->get('name');
			$row->type = 'plugin';
			$row->ordering = 0;
			$row->element = $element;
			$row->folder = $group;
			$row->enabled = 0;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = 0;
			$row->params = $this->parent->getParams();

			// Custom data
			$row->custom_data = '';

			// System data
			$row->system_data = '';
			$row->manifest_cache = $this->parent->generateManifestCache();

			// Editor plugins are published by default
			if ($group == 'editors')
			{
				$row->enabled = 1;
			}

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent
					->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
				);

				return false;
			}

			// Since we have created a plugin item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array('type' => 'extension', 'id' => $row->extension_id));
			$id = $row->extension_id;
		}

		// Let's run the queries for the plugin
		if (strtolower($this->route) == 'install')
		{
			$result = $this->parent->parseSQLFiles($this->manifest->install->sql);

			if ($result === false)
			{
				// Install failed, rollback changes
				$this->parent
					->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_SQL_ERROR', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
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
					$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_UPDATE_SQL_ERROR', $db->stderr(true)));

					return false;
				}
			}
		}

		// Run the custom method based on the route
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $this->route))
		{
			if ($this->parent->manifestClass->{$this->route}($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_PLG_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_COPY_SETUP', JText::_('JLIB_INSTALLER_' . $this->route)));

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

		return $id;
	}

	/**
	 * Custom update method
	 *
	 * @return   boolean  True on success
	 *
	 * @since    3.1
	 */
	public function update()
	{
		// Set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);

		// Set the route for the install
		$this->route = 'update';

		// Go to install which handles updates properly
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The id of the plugin to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$this->route = 'uninstall';

		$row = null;
		$retval = true;
		$db = $this->parent->getDbo();

		// First order of business will be to load the plugin object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_ERRORUNKOWNEXTENSION'), JLog::WARNING, 'jerror');

			return false;
		}

		// Is the plugin we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_WARNCOREPLUGIN', $row->name), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the plugin folder so we can properly build the plugin path
		if (trim($row->folder) == '')
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_FOLDER_FIELD_EMPTY'), JLog::WARNING, 'jerror');

			return false;
		}

		// Set the plugin root path
		$this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);

		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();

		// Attempt to load the language file; might have uninstall strings
		$this->parent->setPath('source', JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);
		$this->loadLanguage(JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		// If there is an manifest class file, let's load it; we'll copy it later (don't have dest yet)
		$manifestScript = (string) $this->manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}
			// If a dash is present in the folder, remove it
			$folderClass = str_replace('-', '', $row->folder);

			// Set the class name
			$classname = 'plg' . $folderClass . $row->element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
			}
		}

		// Run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight($this->route, $this) === false)
			{
				// Preflight failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_PLG_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create the $msg object and append messages from preflight
		$msg = ob_get_contents();
		ob_end_clean();

		// Let's run the queries for the plugin
		$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_UNINSTALL_SQL_ERROR', $db->stderr(true)));

			return false;
		}

		// Run the custom uninstall method if possible
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'uninstall'))
		{
			$this->parent->manifestClass->uninstall($this);
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		// Remove the plugin files
		$this->parent->removeFiles($this->manifest->files, -1);

		// Remove all media and languages as well
		$this->parent->removeFiles($this->manifest->media);
		$this->parent->removeFiles($this->manifest->languages, 1);

		// Remove the schema version
		$query = $db->getQuery(true)
			->delete('#__schemas')
			->where('extension_id = ' . $row->extension_id);
		$db->setQuery($query);
		$db->execute();

		// Now we will no longer need the plugin object, so let's delete it
		$row->delete($row->extension_id);
		unset($row);

		// Remove the plugin's folder
		JFolder::delete($this->parent->getPath('extension_root'));

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		return $retval;
	}

	/**
	 * Custom discover method
	 *
	 * @return  array  JExtension) list of extensions available
	 *
	 * @since   3.1
	 */
	public function discover()
	{
		$results = array();
		$folder_list = JFolder::folders(JPATH_SITE . '/plugins');

		foreach ($folder_list as $folder)
		{
			$file_list = JFolder::files(JPATH_SITE . '/plugins/' . $folder, '\.xml$');

			foreach ($file_list as $file)
			{
				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_SITE . '/plugins/' . $folder . '/' . $file);
				$file = JFile::stripExt($file);

				// Ignore example plugins
				if ($file == 'example')
				{
					continue;
				}

				$extension = JTable::getInstance('extension');
				$extension->set('type', 'plugin');
				$extension->set('client_id', 0);
				$extension->set('element', $file);
				$extension->set('folder', $folder);
				$extension->set('name', $file);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
			$folder_list = JFolder::folders(JPATH_SITE . '/plugins/' . $folder);

			foreach ($folder_list as $plugin_folder)
			{
				$file_list = JFolder::files(JPATH_SITE . '/plugins/' . $folder . '/' . $plugin_folder, '\.xml$');

				foreach ($file_list as $file)
				{
					$manifest_details = JInstaller::parseXMLInstallFile(
						JPATH_SITE . '/plugins/' . $folder . '/' . $plugin_folder . '/' . $file
					);
					$file = JFile::stripExt($file);

					if ($file == 'example')
					{
						continue;
					}

					// Ignore example plugins
					$extension = JTable::getInstance('extension');
					$extension->set('type', 'plugin');
					$extension->set('client_id', 0);
					$extension->set('element', $file);
					$extension->set('folder', $folder);
					$extension->set('name', $file);
					$extension->set('state', -1);
					$extension->set('manifest_cache', json_encode($manifest_details));
					$extension->set('params', '{}');
					$results[] = $extension;
				}
			}
		}
		return $results;
	}

	/**
	 * Custom discover_install method.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function discover_install()
	{
		/*
		 * Plugins use the extensions table as their primary store
		 * Similar to modules and templates, rather easy
		 * If it's not in the extensions table we just add it
		 */
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);

		if (is_dir($client->path . '/plugins/' . $this->parent->extension->folder . '/' . $this->parent->extension->element))
		{
			$manifestPath = $client->path . '/plugins/' . $this->parent->extension->folder . '/' . $this->parent->extension->element . '/'
				. $this->parent->extension->element . '.xml';
		}
		else
		{
			$manifestPath = $client->path . '/plugins/' . $this->parent->extension->folder . '/' . $this->parent->extension->element . '.xml';
		}
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
		$manifest_details = JInstaller::parseXMLInstallFile($manifestPath);
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = ('editors' == $this->parent->extension->folder) ? 1 : 0;
		$this->parent->extension->params = $this->parent->getParams();

		if ($this->parent->extension->store())
		{
			return $this->parent->extension->get('extension_id');
		}
		else
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PLG_DISCOVER_STORE_DETAILS'), JLog::WARNING, 'jerror');

			return false;
		}
	}

	/**
	 * Refreshes the extension table cache.
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure.
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		/*
		 * Plugins use the extensions table as their primary store
		 * Similar to modules and templates, rather easy
		 * If it's not in the extensions table we just add it
		 */
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/plugins/' . $this->parent->extension->folder . '/' . $this->parent->extension->element . '/'
			. $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);

		$this->parent->extension->name = $manifest_details['name'];

		if ($this->parent->extension->store())
		{
			return true;
		}
		else
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PLG_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}
}

/**
 * Deprecated class placeholder. You should use JInstallerAdapterPlugin instead.
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerPlugin extends JInstallerAdapterPlugin
{
}
