<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');
jimport('joomla.installer.packagemanifest');

/**
 * Package installer
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 */
class JInstallerPackage extends JAdapterInstance
{
	/**
	 * Method of system
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $route = 'install';

	/**
	 * Load language from a path
	 *
	 * @param   string  $path  The path of the language.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function loadLanguage($path)
	{
		$this->manifest = $this->parent->getManifest();
		$extension = 'pkg_' . strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->packagename, 'cmd'));
		$lang = JFactory::getLanguage();
		$source = $path;
		$lang->load($extension . '.sys', $source, null, false, false)
			|| $lang->load($extension . '.sys', JPATH_SITE, null, false, false)
			|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
			|| $lang->load($extension . '.sys', JPATH_SITE, $lang->getDefault(), false, false);
	}

	/**
	 * Custom install method
	 *
	 * @return  int  The extension id
	 *
	 * @since   11.1
	 */
	public function install()
	{
		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		// Manifest Document Setup Section

		// Set the extensions name
		$filter = JFilterInput::getInstance();
		$name = (string) $this->manifest->packagename;
		$name = $filter->clean($name, 'cmd');
		$this->set('name', $name);

		$element = 'pkg_' . $filter->clean($this->manifest->packagename, 'cmd');
		$this->set('element', $element);

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

		// Set the installation path
		$files = $this->manifest->files;
		$group = (string) $this->manifest->packagename;
		if (!empty($group))
		{
			$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . implode(DS, explode('/', $group)));
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_PACK', JText::_('JLIB_INSTALLER_' . strtoupper($this->route))));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */
		// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
		$this->scriptElement = $this->manifest->scriptfile;
		$manifestScript = (string) $this->manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// load the file
				include_once $manifestScriptFile;
			}

			// Set the class name
			$classname = $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// create a new instance
				$this->parent->manifestClass = new $classname($this);
				// and set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
				// Note: if we don't find the class, don't bother to copy the file
			}
		}

		// run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight($this->route, $this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_PACKAGE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

		// Filesystem Processing Section

		if ($folder = $files->attributes()->folder)
		{
			$source = $this->parent->getPath('source') . '/' . $folder;
		}
		else
		{
			$source = $this->parent->getPath('source');
		}

		// Install all necessary files
		if (count($this->manifest->files->children()))
		{
			$i = 0;
			foreach ($this->manifest->files->children() as $child)
			{
				$file = $source . '/' . $child;
				if (is_dir($file))
				{
					// If it's actually a directory then fill it up
					$package = array();
					$package['dir'] = $file;
					$package['type'] = JInstallerHelper::detectType($file);
				}
				else
				{
					// If it's an archive
					$package = JInstallerHelper::unpack($file);
				}
				$tmpInstaller = new JInstaller;
				if (!$tmpInstaller->install($package['dir']))
				{
					$this->parent->abort(
						JText::sprintf(
							'JLIB_INSTALLER_ABORT_PACK_INSTALL_ERROR_EXTENSION', JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
							basename($file)
						)
					);
					return false;
				}
				else
				{
					$results[$i] = array(
						'name' => $tmpInstaller->manifest->name,
						'result' => $tmpInstaller->install($package['dir'])
					);
				}
				$i++;
			}
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES', JText::_('JLIB_INSTALLER_' . strtoupper($this->route))));
			return false;
		}

		// Parse optional tags
		$this->parent->parseLanguages($this->manifest->languages);

		// Extension Registration

		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => strtolower($this->get('element')), 'type' => 'package'));
		if ($eid)
		{
			$row->load($eid);
		}
		else
		{
			$row->name = $this->get('name');
			$row->type = 'package';
			$row->element = $this->get('element');
			// There is no folder for modules
			$row->folder = '';
			$row->enabled = 1;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = 0;
			// custom data
			$row->custom_data = '';
			$row->params = $this->parent->getParams();
		}
		// Update the manifest cache for the entry
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_ROLLBACK', $row->getError()));
			return false;
		}

		// Finalization and Cleanup Section

		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $this->route))
		{
			if ($this->parent->manifestClass->{$this->route}($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/packages/' . basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$this->parent->abort(
				JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_COPY_SETUP', JText::_('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES'))
			);
			return false;
		}

		// If there is a manifest script, let's copy it.
		if ($this->get('manifest_script'))
		{
			// First, we have to create a folder for the script if one isn't present
			$created = false;

			if (!file_exists($this->parent->getPath('extension_root')))
			{
				JFolder::create($this->parent->getPath('extension_root'));
			}

			$path['src'] = $this->parent->getPath('source') . '/' . $this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->getOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_PACKAGE_INSTALL_MANIFEST'));

					return false;
				}
			}
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight($this->route, $this, $results);
		}

		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}
		return $row->extension_id;
	}

	/**
	 * Updates a package
	 *
	 * The only difference between an update and a full install
	 * is how we handle the database
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function update()
	{
		$this->route = 'update';
		$this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The id of the package to uninstall.
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

		$row = JTable::getInstance('extension');
		$row->load($id);

		if ($row->protected)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_WARNCOREPACK'));
			return false;
		}

		$manifestFile = JPATH_MANIFESTS . '/packages/' . $row->get('element') . '.xml';
		$manifest = new JPackageManifest($manifestFile);

		// Set the package root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . $manifest->packagename);

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			// TODO: Fail?
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
			return false;

		}

		$xml = JFactory::getXML($manifestFile);

		// If we cannot load the XML file return false
		if (!$xml)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatibility in a future version
		 * Should be 'extension', but for backward compatibility we will accept 'install'.
		 */
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
			return false;
		}

		// If there is an manifest class file, let's load it
		$this->scriptElement = $manifest->scriptfile;
		$manifestScript = (string) $manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('extension_root') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}

			// Set the class name
			$classname = $row->element . 'InstallerScript';

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

		$error = false;
		foreach ($manifest->filelist as $extension)
		{
			$tmpInstaller = new JInstaller;
			$id = $this->_getExtensionID($extension->type, $extension->id, $extension->client, $extension->group);
			$client = JApplicationHelper::getClientInfo($extension->client, true);
			if ($id)
			{
				if (!$tmpInstaller->uninstall($extension->type, $id, $client->id))
				{
					$error = true;
					JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)));
				}
			}
			else
			{
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'));
			}
		}

		// Remove any language files
		$this->parent->removeFiles($xml->languages);

		// clean up manifest file after we're done if there were no errors
		if (!$error)
		{
			JFile::delete($manifestFile);
			$folder = $this->parent->getPath('extension_root');
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
			$row->delete();
		}
		else
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
		}

		// Return the result up the line
		return $retval;
	}

	/**
	 * Gets the extension id.
	 *
	 * @param   string   $type    The extension type.
	 * @param   string   $id      The name of the extension (the element field).
	 * @param   integer  $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
	 * @param   string   $group   The extension group (mainly for plugins).
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getExtensionID($type, $id, $client, $group)
	{
		$db = $this->parent->getDbo();
		$result = $id;

		$query = $db->getQuery(true);
		$query->select('extension_id');
		$query->from('#__extensions');
		$query->where('type = ' . $db->Quote($type));
		$query->where('element = ' . $db->Quote($id));

		switch ($type)
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where('folder = ' . $db->Quote($group));
				break;

			case 'library':
			case 'package':
			case 'component':
				// Components, packages and libraries don't have a folder or client.
				// Included for completeness.
				break;

			case 'language':
			case 'module':
			case 'template':
				// Languages, modules and templates have a client but not a folder
				$client = JApplicationHelper::getClientInfo($client, true);
				$query->where('client_id = ' . (int) $client->id);
				break;
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		// Note: For templates, libraries and packages their unique name is their key.
		// This means they come out the same way they came in.
		return $result;
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure
	 *
	 * @since   11.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS . '/packages/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try
		{
			return $this->parent->extension->store();
		}
		catch (JException $e)
		{
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_PACK_REFRESH_MANIFEST_CACHE'));
			return false;
		}
	}
}
