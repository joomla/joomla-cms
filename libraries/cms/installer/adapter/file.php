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
 * File installer
 *
 * @since  3.1
 */
class JInstallerAdapterFile extends JInstallerAdapter
{
	/**
	 * `<scriptfile>` element of the extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $scriptElement = null;

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
	 * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function copyBaseFiles()
	{
		// Populate File and Folder List to copy
		$this->populateFilesAndFolderList();

		// Now that we have folder list, lets start creating them
		foreach ($this->folderList as $folder)
		{
			if (!JFolder::exists($folder))
			{
				if (!$created = JFolder::create($folder))
				{
					throw new RuntimeException(
						JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $folder)
					);
				}

				// Since we created a directory and will want to remove it if we have to roll back.
				// The installation due to some errors, let's add it to the installation step stack.
				if ($created)
				{
					$this->parent->pushStep(array('type' => 'folder', 'path' => $folder));
				}
			}
		}

		// Now that we have file list, let's start copying them
		$this->parent->copyFiles($this->fileList);
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
		$manifest = array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/files/' . basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_COPY_SETUP'));
		}

		// If there is a manifest script, let's copy it.
		if ($this->manifest_script)
		{
			// First, we have to create a folder for the script if one isn't present
			if (!file_exists($this->parent->getPath('extension_root')))
			{
				JFolder::create($this->parent->getPath('extension_root'));
			}

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
	 * @param   string  $path  The path on which to find language files.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path)
	{
		$extension = 'files_' . strtolower(str_replace('files_', '', $this->getElement()));

		$this->doLoadLanguage($extension, $path, JPATH_SITE);
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
		// Parse optional tags
		$this->parent->parseLanguages($this->getManifest()->languages);
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setupInstallPaths()
	{
		// Set the file root path
		if ($this->name == 'files_joomla')
		{
			// If we are updating the Joomla core, set the root path to the root of Joomla
			$this->parent->setPath('extension_root', JPATH_ROOT);
		}
		else
		{
			$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/files/' . $this->element);
		}
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
			// Load the entry and update the manifest_cache
			$this->extension->load($this->currentExtensionId);

			// Update name
			$this->extension->name = $this->name;

			// Update manifest
			$this->extension->manifest_cache = $this->parent->generateManifestCache();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_ROLLBACK',
						JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						$this->extension->getError()
					)
				);
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults
			$this->extension->name = $this->name;
			$this->extension->type = 'file';
			$this->extension->element = $this->element;

			// There is no folder for files so leave it blank
			$this->extension->folder = '';
			$this->extension->enabled = 1;
			$this->extension->protected = 0;
			$this->extension->access = 0;
			$this->extension->client_id = 0;
			$this->extension->params = '';
			$this->extension->system_data = '';
			$this->extension->manifest_cache = $this->parent->generateManifestCache();
			$this->extension->custom_data = '';

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_ROLLBACK',
						JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						$this->extension->getError()
					)
				);
			}

			// Since we have installed a file extension, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array('type' => 'extension', 'id' => $this->extension->extension_id));
		}
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   string  $id  The id of the file to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$row = JTable::getInstance('extension');

		if (!$row->load($id))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_ENTRY'), JLog::WARNING, 'jerror');

			return false;
		}

		if ($row->protected)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_WARNCOREFILE'), JLog::WARNING, 'jerror');

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

		$retval = true;
		$manifestFile = JPATH_MANIFESTS . '/files/' . $row->element . '.xml';

		// Because files may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			// Set the files root path
			$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/files/' . $row->element);

			$xml = simplexml_load_file($manifestFile);

			// If we cannot load the XML file return null
			if (!$xml)
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_MANIFEST'), JLog::WARNING, 'jerror');

				return false;
			}

			// Check for a valid XML root tag.
			if ($xml->getName() != 'extension')
			{
				JLog::add(JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_MANIFEST'), JLog::WARNING, 'jerror');

				return false;
			}

			$this->setManifest($xml);

			// If there is an manifest class file, let's load it
			$this->scriptElement = $this->getManifest()->scriptfile;
			$manifestScript = (string) $this->getManifest()->scriptfile;

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
					$this->set('manifest_script', $manifestScript);
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

			$db = JFactory::getDbo();

			// Let's run the uninstall queries for the extension
			$result = $this->parent->parseSQLFiles($this->getManifest()->uninstall->sql);

			if ($result === false)
			{
				// Install failed, rollback changes
				JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');
				$retval = false;
			}

			// Remove the schema version
			$query = $db->getQuery(true)
				->delete('#__schemas')
				->where('extension_id = ' . $row->extension_id);
			$db->setQuery($query);
			$db->execute();

			// Loop through all elements and get list of files and folders
			foreach ($xml->fileset->files as $eFiles)
			{
				$target = (string) $eFiles->attributes()->target;

				// Create folder path
				if (empty($target))
				{
					$targetFolder = JPATH_ROOT;
				}
				else
				{
					$targetFolder = JPATH_ROOT . '/' . $target;
				}

				$folderList = array();

				// Check if all children exists
				if (count($eFiles->children()) > 0)
				{
					// Loop through all filenames elements
					foreach ($eFiles->children() as $eFileName)
					{
						if ($eFileName->getName() == 'folder')
						{
							$folderList[] = $targetFolder . '/' . $eFileName;
						}
						else
						{
							$fileName = $targetFolder . '/' . $eFileName;
							JFile::delete($fileName);
						}
					}
				}

				// Delete any folders that don't have any content in them.
				foreach ($folderList as $folder)
				{
					$files = JFolder::files($folder);

					if (!count($files))
					{
						JFolder::delete($folder);
					}
				}
			}

			JFile::delete($manifestFile);

			// Lastly, remove the extension_root
			$folder = $this->parent->getPath('extension_root');

			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
		else
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_NOTFOUND_MANIFEST'), JLog::WARNING, 'jerror');

			// Delete the row because its broken
			$row->delete();

			return false;
		}

		$this->parent->removeFiles($xml->languages);

		$row->delete();

		return $retval;
	}

	/**
	 * Function used to check if extension is already installed
	 *
	 * @param   string  $extension  The element name of the extension to install
	 *
	 * @return  boolean  True if extension exists
	 *
	 * @since   3.1
	 */
	protected function extensionExistsInSystem($extension = null)
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('file'))
			->where($db->quoteName('element') . ' = ' . $db->quote($extension));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', $db->stderr(true)));

			return false;
		}
		$id = $db->loadResult();

		if (empty($id))
		{
			return false;
		}

		return true;
	}

	/**
	 * Function used to populate files and folder list
	 *
	 * @return  boolean  none
	 *
	 * @since   3.1
	 */
	protected function populateFilesAndFolderList()
	{
		// Initialise variable
		$this->folderList = array();
		$this->fileList = array();

		// Set root folder names
		$packagePath = $this->parent->getPath('source');
		$jRootPath = JPath::clean(JPATH_ROOT);

		// Loop through all elements and get list of files and folders
		foreach ($this->getManifest()->fileset->files as $eFiles)
		{
			// Check if the element is files element
			$folder = (string) $eFiles->attributes()->folder;
			$target = (string) $eFiles->attributes()->target;

			// Split folder names into array to get folder names. This will help in creating folders
			$arrList = preg_split("#/|\\/#", $target);

			$folderName = $jRootPath;

			foreach ($arrList as $dir)
			{
				if (empty($dir))
				{
					continue;
				}

				$folderName .= '/' . $dir;

				// Check if folder exists, if not then add to the array for folder creation
				if (!JFolder::exists($folderName))
				{
					$this->folderList[] = $folderName;
				}
			}

			// Create folder path
			$sourceFolder = empty($folder) ? $packagePath : $packagePath . '/' . $folder;
			$targetFolder = empty($target) ? $jRootPath : $jRootPath . '/' . $target;

			// Check if source folder exists
			if (!JFolder::exists($sourceFolder))
			{
				JLog::add(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $sourceFolder), JLog::WARNING, 'jerror');

				// If installation fails, rollback
				$this->parent->abort();

				return false;
			}

			// Check if all children exists
			if (count($eFiles->children()))
			{
				// Loop through all filenames elements
				foreach ($eFiles->children() as $eFileName)
				{
					$path['src'] = $sourceFolder . '/' . $eFileName;
					$path['dest'] = $targetFolder . '/' . $eFileName;
					$path['type'] = 'file';

					if ($eFileName->getName() == 'folder')
					{
						$folderName         = $targetFolder . '/' . $eFileName;
						$this->folderList[] = $folderName;
						$path['type']       = 'folder';
					}

					$this->fileList[] = $path;
				}
			}
			else
			{
				$files = JFolder::files($sourceFolder);

				foreach ($files as $file)
				{
					$path['src'] = $sourceFolder . '/' . $file;
					$path['dest'] = $targetFolder . '/' . $file;

					$this->fileList[] = $path;
				}
			}
		}
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean result of operation, true if updated, false on failure
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS . '/files/' . $this->parent->extension->element . '.xml';
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

/**
 * Deprecated class placeholder. You should use JInstallerAdapterFile instead.
 *
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerFile extends JInstallerAdapterFile
{
}
