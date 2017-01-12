<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Plugin installer
 *
 * @since  3.1
 */
class JInstallerAdapterPlugin extends JInstallerAdapter
{
	/**
	 * `<scriptfile>` element of the extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $scriptElement = null;

	/**
	 * `<files>` element of the old extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $oldFiles = null;

	/**
	 * Method to check if the extension is already present in the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function checkExistingExtension()
	{
		try
		{
			$this->currentExtensionId = $this->extension->find(
				array('type' => $this->type, 'element' => $this->element, 'folder' => $this->group)
			);
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_ROLLBACK',
					JText::_('JLIB_INSTALLER_' . $this->route),
					$e->getMessage()
				),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function copyBaseFiles()
	{
		// Copy all necessary files
		if ($this->parent->parseFiles($this->getManifest()->files, -1, $this->oldFiles) === false)
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_PLG_COPY_FILES',
					JText::_('JLIB_INSTALLER_' . $this->route)
				)
			);
		}

		// If there is a manifest script, let's copy it.
		if ($this->manifest_script)
		{
			$path['src']  = $this->parent->getPath('source') . '/' . $this->manifest_script;
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					throw new RuntimeException(
						JText::sprintf(
							'JLIB_INSTALLER_ABORT_PLG_INSTALL_MANIFEST',
							JText::_('JLIB_INSTALLER_' . $this->route)
						)
					);
				}
			}
		}
	}

	/**
	 * Method to create the extension root path if necessary
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function createExtensionRoot()
	{
		// Run the common create code first
		parent::createExtensionRoot();

		// If we're updating at this point when there is always going to be an extension_root find the old XML files
		if ($this->route === 'update')
		{
			// Create a new installer because findManifest sets stuff; side effects!
			$tmpInstaller = new JInstaller;

			// Look in the extension root
			$tmpInstaller->setPath('source', $this->parent->getPath('extension_root'));

			if ($tmpInstaller->findManifest())
			{
				$old_manifest   = $tmpInstaller->getManifest();
				$this->oldFiles = $old_manifest->files;
			}
		}
	}

	/**
	 * Method to finalise the installation processing
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function finaliseInstall()
	{
		// Clobber any possible pending updates
		/** @var JTableUpdate $update */
		$update = JTable::getInstance('update');
		$uid = $update->find(
			array(
				'element' => $this->element,
				'type'    => $this->type,
				'folder'  => $this->group,
			)
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if ($this->route !== 'discover_install')
		{
			if (!$this->parent->copyManifest(-1))
			{
				// Install failed, rollback changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_COPY_SETUP',
						JText::_('JLIB_INSTALLER_' . $this->route)
					)
				);
			}
		}
	}

	/**
	 * Get the filtered extension element from the manifest
	 *
	 * @param   string  $element  Optional element name to be converted
	 *
	 * @return  string  The filtered element
	 *
	 * @since   3.4
	 */
	public function getElement($element = null)
	{
		if (!$element)
		{
			// Backward Compatibility
			// @todo Deprecate in future version
			if (count($this->getManifest()->files->children()))
			{
				$type = (string) $this->getManifest()->attributes()->type;

				foreach ($this->getManifest()->files->children() as $file)
				{
					if ((string) $file->attributes()->$type)
					{
						$element = (string) $file->attributes()->$type;

						break;
					}
				}
			}
		}

		return $element;
	}

	/**
	 * Get the class name for the install adapter script.
	 *
	 * @return  string  The class name.
	 *
	 * @since   3.4
	 */
	protected function getScriptClassName()
	{
		return 'Plg' . str_replace('-', '', $this->group) . $this->element . 'InstallerScript';
	}

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
			$this->parent->setPath(
				'source',
				JPATH_PLUGINS . '/' . $this->parent->extension->folder . '/' . $this->parent->extension->element
			);
		}

		$element = $this->getManifest()->files;

		if ($element)
		{
			$group = strtolower((string) $this->getManifest()->attributes()->group);
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
				$source = $path ? $path : JPATH_PLUGINS . "/$group/$name";
				$folder = (string) $element->attributes()->folder;

				if ($folder && file_exists("$path/$folder"))
				{
					$source = "$path/$folder";
				}

				$this->doLoadLanguage($extension, $source, JPATH_ADMINISTRATOR);
			}
		}
	}

	/**
	 * Method to parse optional tags in the manifest
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function parseOptionalTags()
	{
		// Parse optional tags -- media and language files for plugins go in admin app
		$this->parent->parseMedia($this->getManifest()->media, 1);
		$this->parent->parseLanguages($this->getManifest()->languages, 1);
	}

	/**
	 * Prepares the adapter for a discover_install task
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function prepareDiscoverInstall()
	{
		$client   = JApplicationHelper::getClientInfo($this->extension->client_id);
		$basePath = $client->path . '/plugins/' . $this->extension->folder;

		if (is_dir($basePath . '/' . $this->extension->element))
		{
			$manifestPath = $basePath . '/' . $this->extension->element . '/' . $this->extension->element . '.xml';
		}
		else
		{
			// @deprecated 4.0 - This path supports Joomla! 1.5 plugin folder layouts
			$manifestPath = $basePath . '/' . $this->extension->element . '.xml';
		}

		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->setManifest($this->parent->getManifest());
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function setupInstallPaths()
	{
		$this->group = (string) $this->getManifest()->attributes()->group;

		if (empty($this->element) && empty($this->group))
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_PLG_INSTALL_NO_FILE',
					JText::_('JLIB_INSTALLER_' . $this->route)
				)
			);
		}

		$this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $this->group . '/' . $this->element);
	}

	/**
	 * Method to store the extension to the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function storeExtension()
	{
		// Discover installs are stored a little differently
		if ($this->route === 'discover_install')
		{
			$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));

			$this->extension->manifest_cache = json_encode($manifest_details);
			$this->extension->state = 0;
			$this->extension->name = $manifest_details['name'];
			$this->extension->enabled = ('editors' === $this->extension->folder) ? 1 : 0;
			$this->extension->params = $this->parent->getParams();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ERROR_PLG_DISCOVER_STORE_DETAILS'));
			}

			return;
		}

		// Was there a plugin with the same name already installed?
		if ($this->currentExtensionId)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_ALLREADY_EXISTS',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->name
					)
				);
			}

			$this->extension->load($this->currentExtensionId);
			$this->extension->name = $this->name;
			$this->extension->manifest_cache = $this->parent->generateManifestCache();

			// Update the manifest cache and name
			$this->extension->store();
		}
		else
		{
			// Store in the extensions table (1.6)
			$this->extension->name = $this->name;
			$this->extension->type = 'plugin';
			$this->extension->ordering = 0;
			$this->extension->element = $this->element;
			$this->extension->folder = $this->group;
			$this->extension->enabled = 0;
			$this->extension->protected = 0;
			$this->extension->access = 1;
			$this->extension->client_id = 0;
			$this->extension->params = $this->parent->getParams();

			// Custom data
			$this->extension->custom_data = '';

			// System data
			$this->extension->system_data = '';
			$this->extension->manifest_cache = $this->parent->generateManifestCache();

			// Editor plugins are published by default
			if ($this->group === 'editors')
			{
				$this->extension->enabled = 1;
			}

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PLG_INSTALL_ROLLBACK',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->extension->getError()
					)
				);
			}

			// Since we have created a plugin item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array('type' => 'extension', 'id' => $this->extension->extension_id));
		}
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

		/*
		 * Does this extension have a parent package?
		 * If so, check if the package disallows individual extensions being uninstalled if the package is not being uninstalled
		 */
		if ($row->package_id && !$this->parent->isPackageUninstall() && !$this->canUninstallPackageChild($row->package_id))
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_CANNOT_UNINSTALL_CHILD_OF_PACKAGE', $row->name), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the plugin folder so we can properly build the plugin path
		if (trim($row->folder) === '')
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_FOLDER_FIELD_EMPTY'), JLog::WARNING, 'jerror');

			return false;
		}

		// Set the plugin root path
		$this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);

		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		$this->parent->findManifest();
		$this->setManifest($this->parent->getManifest());

		// Attempt to load the language file; might have uninstall strings
		$this->parent->setPath('source', JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);
		$this->loadLanguage(JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		// If there is an manifest class file, let's load it; we'll copy it later (don't have dest yet)
		$manifestScript = (string) $this->getManifest()->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			// If a dash is present in the folder, remove it
			$folderClass = str_replace('-', '', $row->folder);

			// Set the class name
			$classname = 'Plg' . $folderClass . $row->element . 'InstallerScript';

			JLoader::register($classname, $manifestScriptFile);

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
		$utfresult = $this->parent->parseSQLFiles($this->getManifest()->uninstall->sql);

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
		$this->parent->removeFiles($this->getManifest()->files, -1);

		// Remove all media and languages as well
		$this->parent->removeFiles($this->getManifest()->media);
		$this->parent->removeFiles($this->getManifest()->languages, 1);

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
				if ($file === 'example' || $manifest_details === false)
				{
					continue;
				}

				$element = empty($manifest_details['filename']) ? $file : $manifest_details['filename'];

				$extension = JTable::getInstance('extension');
				$extension->set('type', 'plugin');
				$extension->set('client_id', 0);
				$extension->set('element', $element);
				$extension->set('folder', $folder);
				$extension->set('name', $manifest_details['name']);
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

					if ($file === 'example' || $manifest_details === false)
					{
						continue;
					}

					$element = empty($manifest_details['filename']) ? $file : $manifest_details['filename'];

					// Ignore example plugins
					$extension = JTable::getInstance('extension');
					$extension->set('type', 'plugin');
					$extension->set('client_id', 0);
					$extension->set('element', $element);
					$extension->set('folder', $folder);
					$extension->set('name', $manifest_details['name']);
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
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerPlugin extends JInstallerAdapterPlugin
{
}
