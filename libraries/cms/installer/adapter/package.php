<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Package installer
 *
 * @since  3.1
 */
class JInstallerAdapterPackage extends JInstallerAdapter
{
	/**
	 * An array of extension IDs for each installed extension
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	protected $installedIds = array();

	/**
	 * The results of each installed extensions
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $results = array();

	/**
	 * Flag if the adapter supports discover installs
	 *
	 * Adapters should override this and set to false if discover install is unsupported
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $supportsDiscoverInstall = false;

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
		// If the package manifest already exists, then we will assume that the package is already installed.
		if (file_exists(JPATH_MANIFESTS . '/packages/' . basename($this->parent->getPath('manifest'))))
		{
			// Look for an update function or update tag
			$updateElement = $this->manifest->update;

			// Upgrade manually set or update function available or update tag detected
			if ($this->parent->isUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
				|| $updateElement)
			{
				// Force this one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);

				if ($this->currentExtensionId)
				{
					// If there is a matching extension mark this as an update
					$this->setRoute('update');
				}
			}
			elseif (!$this->parent->isOverwrite())
			{
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_DIRECTORY',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->type,
						$this->parent->getPath('extension_root')
					)
				);
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
		$folder = (string) $this->getManifest()->files->attributes()->folder;
		$source = $this->parent->getPath('source');

		if ($folder)
		{
			$source .= '/' . $folder;
		}

		// Install all necessary files
		if (!count($this->getManifest()->files->children()))
		{
			throw new RuntimeException(
				JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES',
					JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$dispatcher = JFactory::getApplication()->getDispatcher();

		// Add a callback for the `onExtensionAfterInstall` event so we can receive the installed extension ID
		if (!$dispatcher->hasListener([$this, 'onExtensionAfterInstall'], 'onExtensionAfterInstall'))
		{
			$dispatcher->addListener('onExtensionAfterInstall', [$this, 'onExtensionAfterInstall']);
		}

		foreach ($this->getManifest()->files->children() as $child)
		{
			$file = $source . '/' . (string) $child;

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

			$tmpInstaller  = new JInstaller;
			$installResult = $tmpInstaller->install($package['dir']);

			if (!$installResult)
			{
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_PACK_INSTALL_ERROR_EXTENSION',
						JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						basename($file)
					)
				);
			}

			$this->results[] = array(
				'name'   => (string) $tmpInstaller->manifest->name,
				'result' => $installResult,
			);
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
		/*
		 * For packages, we only need the extension root if copying manifest files; this step will be handled
		 * at that point if necessary
		 */
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

		// Set the package ID for each of the installed extensions to track the relationship
		if (!empty($this->installedIds))
		{
			$db = $this->db;
			$query = $db->getQuery(true)
				->update('#__extensions')
				->set($db->quoteName('package_id') . ' = ' . (int) $this->extension->extension_id)
				->where($db->quoteName('extension_id') . ' IN (' . implode(', ', $this->installedIds) . ')');

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_SETTING_PACKAGE_ID'), JLog::WARNING, 'jerror');
			}
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/packages/' . basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_PACK_INSTALL_COPY_SETUP',
					JText::_('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES')
				)
			);
		}

		// If there is a manifest script, let's copy it.
		if ($this->manifest_script)
		{
			// First, we have to create a folder for the script if one isn't present
			if (!file_exists($this->parent->getPath('extension_root')))
			{
				if (!JFolder::create($this->parent->getPath('extension_root')))
				{
					throw new RuntimeException(
						JText::sprintf(
							'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
							JText::_('JLIB_INSTALLER_' . $this->route),
							$this->parent->getPath('extension_root')
						)
					);
				}

				/*
				 * Since we created the extension directory and will want to remove it if
				 * we have to roll back the installation, let's add it to the
				 * installation step stack
				 */

				$this->parent->pushStep(
					array(
						'type' => 'folder',
						'path' => $this->parent->getPath('extension_root'),
					)
				);
			}

			$path['src'] = $this->parent->getPath('source') . '/' . $this->manifest_script;
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_PACKAGE_INSTALL_MANIFEST'));
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
			// Ensure the element is a string
			$element = (string) $this->getManifest()->packagename;

			// Filter the name for illegal characters
			$element = 'pkg_' . JFilterInput::getInstance()->clean($element, 'cmd');
		}

		return $element;
	}

	/**
	 * Load language from a path
	 *
	 * @param   string  $path  The path of the language.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path)
	{
		$this->doLoadLanguage($this->getElement(), $path);
	}

	/**
	 * Handler for the `onExtensionAfterInstall` event
	 *
	 * @param   JInstaller       $installer  JInstaller instance managing the extension's installation
	 * @param   integer|boolean  $eid        The extension ID of the installed extension on success, boolean false on install failure
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onExtensionAfterInstall(JInstaller $installer, $eid)
	{
		if ($eid !== false)
		{
			$this->installedIds[] = $eid;
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
		$this->parent->parseLanguages($this->getManifest()->languages);
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
		$packagepath = (string) $this->getManifest()->packagename;

		if (empty($packagepath))
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_PACK',
					JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . $packagepath);
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
		if ($this->currentExtensionId)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_ALREADY_EXISTS',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->name
					)
				);
			}

			$this->extension->load($this->currentExtensionId);
			$this->extension->name = $this->name;
		}
		else
		{
			$this->extension->name = $this->name;
			$this->extension->type = 'package';
			$this->extension->element = $this->element;

			// There is no folder for packages
			$this->extension->folder = '';
			$this->extension->enabled = 1;
			$this->extension->protected = 0;
			$this->extension->access = 1;
			$this->extension->client_id = 0;

			// Custom data
			$this->extension->custom_data = '';
			$this->extension->system_data = '';
			$this->extension->params = $this->parent->getParams();
		}

		// Update the manifest cache for the entry
		$this->extension->manifest_cache = $this->parent->generateManifestCache();

		if (!$this->extension->store())
		{
			// Install failed, roll back changes
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_PACK_INSTALL_ROLLBACK',
					$this->extension->getError()
				)
			);
		}

		// Since we have created a package item, we add it to the installation step stack
		// so that if we have to rollback the changes we can undo it.
		$this->parent->pushStep(array('type' => 'extension', 'id' => $this->extension->extension_id));
	}

	/**
	 * Executes a custom install script method
	 *
	 * @param   string  $method  The install method to execute
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 */
	protected function triggerManifestScript($method)
	{
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $method))
		{
			switch ($method)
			{
				// The preflight method takes the route as a param
				case 'preflight':
					if ($this->parent->manifestClass->$method($this->route, $this) === false)
					{
						// The script failed, rollback changes
						throw new RuntimeException(
							JText::sprintf(
								'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
								JText::_('JLIB_INSTALLER_' . $this->route)
							)
						);
					}

					break;

				// The postflight method takes the route and a results array as params
				case 'postflight':
					$this->parent->manifestClass->$method($this->route, $this, $this->results);

					break;

				// The install, uninstall, and update methods only pass this object as a param
				case 'install':
				case 'uninstall':
				case 'update':
					if ($this->parent->manifestClass->$method($this) === false)
					{
						if ($method != 'uninstall')
						{
							// The script failed, rollback changes
							throw new RuntimeException(
								JText::sprintf(
									'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
									JText::_('JLIB_INSTALLER_' . $this->route)
								)
							);
						}
					}

					break;
			}
		}

		// Append to the message object
		$this->extensionMessage .= ob_get_clean();

		// If in postflight or uninstall, set the message for display
		if (($method == 'uninstall' || $method == 'postflight') && $this->extensionMessage != '')
		{
			$this->parent->set('extension_message', $this->extensionMessage);
		}

		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The id of the package to uninstall.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$row = null;
		$retval = true;

		$row = JTable::getInstance('extension');
		$row->load($id);

		if ($row->protected)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_WARNCOREPACK'), JLog::WARNING, 'jerror');

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

		$manifestFile = JPATH_MANIFESTS . '/packages/' . $row->get('element') . '.xml';
		$manifest = new JInstallerManifestPackage($manifestFile);

		// Set the package root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . $manifest->packagename);

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			// TODO: Fail?
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		$xml = simplexml_load_file($manifestFile);

		// If we cannot load the XML file return false
		if (!$xml)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		// Check for a valid XML root tag.
		if ($xml->getName() != 'extension')
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		// If there is an manifest class file, let's load it
		$manifestScript = (string) $manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('extension_root') . '/' . $manifestScript;

			// Set the class name
			$classname = $row->element . 'InstallerScript';

			JLoader::register($classname, $manifestScriptFile);

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->manifest_script = $manifestScript;
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

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		$error = false;

		foreach ($manifest->filelist as $extension)
		{
			$tmpInstaller = new JInstaller;
			$tmpInstaller->setPackageUninstall(true);

			$id = $this->_getExtensionId($extension->type, $extension->id, $extension->client, $extension->group);
			$client = JApplicationHelper::getClientInfo($extension->client, true);

			if ($id)
			{
				if (!$tmpInstaller->uninstall($extension->type, $id, $client->id))
				{
					$error = true;
					JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)), JLog::WARNING, 'jerror');
				}
			}
			else
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'), JLog::WARNING, 'jerror');
			}
		}

		// Remove any language files
		$this->parent->removeFiles($xml->languages);

		// Clean up manifest file after we're done if there were no errors
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
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'), JLog::WARNING, 'jerror');
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
	 * @since   3.1
	 */
	protected function _getExtensionId($type, $id, $client, $group)
	{
		$db = $this->parent->getDbo();

		$query = $db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where('type = ' . $db->quote($type))
			->where('element = ' . $db->quote($id));

		switch ($type)
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where('folder = ' . $db->quote($group));
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
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS . '/packages/' . $this->parent->extension->element . '.xml';
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
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_PACK_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}
}
