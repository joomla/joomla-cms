<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Library installer
 *
 * @since  3.1
 */
class JInstallerAdapterLibrary extends JInstallerAdapter
{
	/**
	 * Method to check if the extension is present in the filesystem, flags the route as update if so
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function checkExtensionInFilesystem()
	{
		if ($this->currentExtensionId)
		{
			// Already installed, can we upgrade?
			if ($this->parent->isOverwrite() || $this->parent->isUpgrade())
			{
				// We can upgrade, so uninstall the old one
				$installer = new JInstaller; // we don't want to compromise this instance!
				$installer->uninstall('library', $this->currentExtensionId);

				// Clear the cached data
				$this->currentExtensionId = null;
				$this->extension = JTable::getInstance('Extension', 'JTable', array('dbo' => $this->db));

				// From this point we'll consider this an update
				$this->setRoute('update');
			}
			else
			{
				// Abort the install, no upgrade possible
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_ALREADY_INSTALLED'));
			}
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
		if ($this->parent->parseFiles($this->getManifest()->files, -1) === false)
		{
			throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_LIB_COPY_FILES'));
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
				'type' => $this->type,
			)
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if ($this->route != 'discover_install')
		{
			$manifest = array();
			$manifest['src'] = $this->parent->getPath('manifest');
			$manifest['dest'] = JPATH_MANIFESTS . '/libraries/' . basename($this->parent->getPath('manifest'));

			if (!$this->parent->copyFiles(array($manifest), true))
			{
				// Install failed, rollback changes
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_COPY_SETUP'));
			}

			// If there is a manifest script, let's copy it.
			if ($this->manifest_script)
			{
				$path['src'] = $this->parent->getPath('source') . '/' . $this->manifest_script;
				$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

				if (!file_exists($path['dest']) || $this->parent->isOverwrite())
				{
					if (!$this->parent->copyFiles(array($path)))
					{
						// Install failed, rollback changes
						throw new RuntimeException(
							JText::sprintf(
								'JLIB_INSTALLER_ABORT_MANIFEST',
								JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
							)
						);
					}
				}
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
			$manifestPath = JPath::clean($this->parent->getPath('manifest'));
			$element = preg_replace('/\.xml/', '', basename($manifestPath));
		}

		return $element;
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
			$this->parent->setPath('source', JPATH_PLATFORM . '/' . $this->getElement());
		}

		$extension = 'lib_' . $this->getElement();
		$librarypath = (string) $this->getManifest()->libraryname;
		$source = $path ? $path : JPATH_PLATFORM . '/' . $librarypath;

		$this->doLoadLanguage($extension, $source, JPATH_SITE);
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
		$this->parent->parseLanguages($this->getManifest()->languages);
		$this->parent->parseMedia($this->getManifest()->media);
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
		$manifestPath = JPATH_MANIFESTS . '/libraries/' . $this->extension->element . '.xml';
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
		$group = (string) $this->getManifest()->libraryname;

		if (!$group)
		{
			throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_NOFILE'));
		}

		$this->parent->setPath('extension_root', JPATH_PLATFORM . '/' . implode(DIRECTORY_SEPARATOR, explode('/', $group)));
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
		if ($this->route == 'discover_install')
		{
			$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));

			$this->extension->manifest_cache = json_encode($manifest_details);
			$this->extension->state = 0;
			$this->extension->name = $manifest_details['name'];
			$this->extension->enabled = 1;
			$this->extension->params = $this->parent->getParams();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ERROR_LIB_DISCOVER_STORE_DETAILS'));
			}

			return;
		}

		$this->extension->name = $this->name;
		$this->extension->type = 'library';
		$this->extension->element = $this->element;

		// There is no folder for libraries
		$this->extension->folder = '';
		$this->extension->enabled = 1;
		$this->extension->protected = 0;
		$this->extension->access = 1;
		$this->extension->client_id = 0;
		$this->extension->params = $this->parent->getParams();

		// Custom data
		$this->extension->custom_data = '';
		$this->extension->system_data = '';

		// Update the manifest cache for the entry
		$this->extension->manifest_cache = $this->parent->generateManifestCache();

		if (!$this->extension->store())
		{
			// Install failed, roll back changes
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_LIB_INSTALL_ROLLBACK',
					$this->extension->getError()
				)
			);
		}

		// Since we have created a library item, we add it to the installation step stack
		// so that if we have to rollback the changes we can undo it.
		$this->parent->pushStep(array('type' => 'extension', 'id' => $this->extension->extension_id));
	}

	/**
	 * Custom update method
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.1
	 */
	public function update()
	{
		// Since this is just files, an update removes old files
		// Get the extension manifest object
		$this->setManifest($this->parent->getManifest());

		// Set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);

		// And make sure the route is set correctly
		$this->setRoute('update');

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name = (string) $this->getManifest()->name;
		$name = JFilterInput::getInstance()->clean($name, 'string');
		$element = str_replace('.xml', '', basename($this->parent->getPath('manifest')));
		$this->set('name', $name);
		$this->set('element', $element);

		// We don't want to compromise this instance!
		$installer = new JInstaller;
		$db = $this->parent->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('library'))
			->where($db->quoteName('element') . ' = ' . $db->quote($element));
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			// Already installed, which would make sense
			$installer->uninstall('library', $result);

			// Clear the cached data
			$this->currentExtensionId = null;
			$this->extension = JTable::getInstance('Extension', 'JTable', array('dbo' => $this->db));
		}

		// Now create the new files
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   string  $id  The id of the library to uninstall.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$retval = true;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element))
		{
			JLog::add(JText::_('ERRORUNKOWNEXTENSION'), JLog::WARNING, 'jerror');

			return false;
		}

		// Is the library we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_WARNCORELIBRARY'), JLog::WARNING, 'jerror');

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

		$manifestFile = JPATH_MANIFESTS . '/libraries/' . $row->element . '.xml';

		// Because libraries may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			$manifest = new JInstallerManifestLibrary($manifestFile);

			// Set the library root path
			$this->parent->setPath('extension_root', JPATH_PLATFORM . '/' . $manifest->libraryname);

			$xml = simplexml_load_file($manifestFile);

			// If we cannot load the XML file return null
			if (!$xml)
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_LOAD_MANIFEST'), JLog::WARNING, 'jerror');

				return false;
			}

			// Check for a valid XML root tag.
			if ($xml->getName() != 'extension')
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_INVALID_MANIFEST'), JLog::WARNING, 'jerror');

				return false;
			}

			$this->parent->removeFiles($xml->files, -1);
			JFile::delete($manifestFile);
		}
		else
		{
			// Remove this row entry since its invalid
			$row->delete($row->extension_id);
			unset($row);
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_INVALID_NOTFOUND_MANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		// TODO: Change this so it walked up the path backwards so we clobber multiple empties
		// If the folder is empty, let's delete it
		if (JFolder::exists($this->parent->getPath('extension_root')))
		{
			if (is_dir($this->parent->getPath('extension_root')))
			{
				$files = JFolder::files($this->parent->getPath('extension_root'));

				if (!count($files))
				{
					JFolder::delete($this->parent->getPath('extension_root'));
				}
			}
		}

		$this->parent->removeFiles($xml->media);
		$this->parent->removeFiles($xml->languages);

		$row->delete($row->extension_id);
		unset($row);

		return $retval;
	}

	/**
	 * Custom discover method
	 *
	 * @return  array  JExtension  list of extensions available
	 *
	 * @since   3.1
	 */
	public function discover()
	{
		$results = array();
		$file_list = JFolder::files(JPATH_MANIFESTS . '/libraries', '\.xml$');

		foreach ($file_list as $file)
		{
			$manifest_details = JInstaller::parseXMLInstallFile(JPATH_MANIFESTS . '/libraries/' . $file);
			$file = JFile::stripExt($file);
			$extension = JTable::getInstance('extension');
			$extension->set('type', 'library');
			$extension->set('client_id', 0);
			$extension->set('element', $file);
			$extension->set('folder', '');
			$extension->set('name', $file);
			$extension->set('state', -1);
			$extension->set('manifest_cache', json_encode($manifest_details));
			$extension->set('params', '{}');
			$results[] = $extension;
		}

		return $results;
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS . '/libraries/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try
		{
			return $this->parent->extension->store();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LIB_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}
}

/**
 * Deprecated class placeholder. You should use JInstallerAdapterLibrary instead.
 *
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerLibrary extends JInstallerAdapterLibrary
{
}
