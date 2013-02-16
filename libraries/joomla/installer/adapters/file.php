<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.installer.filemanifest');
jimport('joomla.base.adapterinstance');

/**
 * File installer
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 */
class JInstallerFile extends JAdapterInstance
{
	protected $route = 'install';

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path on which to find language files.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function loadLanguage($path)
	{
		$this->manifest = $this->parent->getManifest();
		$extension = 'files_' . str_replace('files_', '', strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd')));
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
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function install()
	{
		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		// Manifest Document Setup Section

		// Set the extension's name
		$name = JFilterInput::getInstance()->clean((string) $this->manifest->name, 'string');
		$this->set('name', $name);

		// Set element
		$manifestPath = JPath::clean($this->parent->getPath('manifest'));
		$element = preg_replace('/\.xml/', '', basename($manifestPath));
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

		//Check if the extension by the same name is already installed
		if ($this->extensionExistsInSystem($element))
		{
			// Package with same name already exists
			if (!$this->parent->isOverwrite())
			{
				// we're not overwriting so abort
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_SAME_NAME'));
				return false;
			}
			else
			{
				// swap to the update route
				$this->route = 'update';
			}
		}
		// Set the file root path
		$this->parent->setPath('extension_root', JPATH_ROOT);

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
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

		// Populate File and Folder List to copy
		$this->populateFilesAndFolderList();

		// Filesystem Processing Section

		// Now that we have folder list, lets start creating them
		foreach ($this->folderList as $folder)
		{
			if (!JFolder::exists($folder))
			{

				if (!$created = JFolder::create($folder))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $folder));
					// If installation fails, rollback
					$this->parent->abort();
					return false;
				}

				// Since we created a directory and will want to remove it if we have to roll back.
				// the installation due to some errors, let's add it to the installation step stack.

				if ($created)
				{
					$this->parent->pushStep(array('type' => 'folder', 'path' => $folder));
				}
			}

		}

		// Now that we have file list, let's start copying them
		$this->parent->copyFiles($this->fileList);

		// Parse optional tags
		$this->parent->parseLanguages($this->manifest->languages);

		// Finalization and Cleanup Section

		// Get a database connector object
		$db = $this->parent->getDbo();

		// Check to see if a module by the same name is already installed
		// If it is, then update the table because if the files aren't there
		// we can assume that it was (badly) uninstalled
		// If it isn't, add an entry to extensions
		$query = $db->getQuery(true);
		$query->select($query->qn('extension_id'))
			->from($query->qn('#__extensions'));
		$query->where($query->qn('type') . ' = ' . $query->q('file'))
			->where($query->qn('element') . ' = ' . $query->q($element));
		$db->setQuery($query);
		try
		{
			$db->execute();
		}
		catch (JException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort(
				JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
			);
			return false;
		}
		$id = $db->loadResult();
		$row = JTable::getInstance('extension');

		if ($id)
		{
			// Load the entry and update the manifest_cache
			$row->load($id);
			// Update name
			$row->set('name', $this->get('name'));
			// Update manifest
			$row->manifest_cache = $this->parent->generateManifestCache();
			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
				);
				return false;
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults
			$row->set('name', $this->get('name'));
			$row->set('type', 'file');
			$row->set('element', $this->get('element'));
			// There is no folder for files so leave it blank
			$row->set('folder', '');
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', 0);
			$row->set('params', '');
			$row->set('system_data', '');
			$row->set('manifest_cache', $this->parent->generateManifestCache());

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_ROLLBACK', $db->stderr(true)));
				return false;
			}

			// Set the insert id
			$row->set('extension_id', $db->insertid());

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array('type' => 'extension', 'extension_id' => $row->extension_id));
		}

		/*
		 * Let's run the queries for the file
		 */
		// second argument is the utf compatible version attribute
		if (strtolower($this->route) == 'install')
		{
			$utfresult = $this->parent->parseSQLFiles($this->manifest->install->sql);

			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_SQL_ERROR', JText::_('JLIB_INSTALLER_' . $this->route), $db->stderr(true))
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
					$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_UPDATE_SQL_ERROR', $db->stderr(true)));
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
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/files/' . basename($this->parent->getPath('manifest'));
		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_COPY_SETUP'));
			return false;
		}

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(
			array('element' => $this->get('element'), 'type' => 'file', 'client_id' => '', 'folder' => '')
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight($this->route, $this);
		}

		$msg .= ob_get_contents(); // append messages
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
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function update()
	{
		// Set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);
		$this->route = 'update';

		// ...and adds new files
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   string  $id  The id of the file to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$row = JTable::getInstance('extension');
		if (!$row->load($id))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_ENTRY'));
			return false;
		}

		if ($row->protected)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_WARNCOREFILE'));
			return false;
		}

		$retval = true;
		$manifestFile = JPATH_MANIFESTS . '/files/' . $row->element . '.xml';

		// Because files may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			// Set the plugin root path
			$this->parent->setPath('extension_root', JPATH_ROOT); // . '/files/' . $manifest->filename);

			$xml = JFactory::getXML($manifestFile);

			// If we cannot load the XML file return null
			if (!$xml)
			{
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_MANIFEST'));
				return false;
			}

			/*
			 * Check for a valid XML root tag.
			 */
			if ($xml->getName() != 'extension')
			{
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_MANIFEST'));
				return false;
			}

			$this->manifest = $xml;

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

			/*
			 * Let's run the uninstall queries for the component
			 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
			 *	file for utf-8 support or non-utf support
			 */
			// Try for Joomla 1.5 type queries
			// Second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

			$db = JFactory::getDbo();
			if ($utfresult === false)
			{
				// Install failed, rollback changes
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_SQL_ERROR', $db->stderr(true)));
				$retval = false;
			}

 			// Remove the schema version
			$query = $db->getQuery(true);
			$query->delete()
				->from('#__schemas')
				->where('extension_id = ' . $row->extension_id);
			$db->setQuery($query);
			$db->execute();

			// Set root folder names
			$packagePath = $this->parent->getPath('source');
			$jRootPath = JPath::clean(JPATH_ROOT);

			// Loop through all elements and get list of files and folders
			foreach ($xml->fileset->files as $eFiles)
			{
				$folder = (string) $eFiles->attributes()->folder;
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

		}
		else
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
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
	 * @since   11.1
	 */
	protected function extensionExistsInSystem($extension = null)
	{
		// Get a database connector object
		$db = $this->parent->getDBO();

		$query = $db->getQuery(true);
		$query->select($query->qn('extension_id'))
			->from($query->qn('#__extensions'));
		$query->where($query->qn('type') . ' = ' . $query->q('file'))
			->where($query->qn('element') . ' = ' . $query->q($extension));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JException $e)
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
	 * @since   11.1
	 */
	protected function populateFilesAndFolderList()
	{

		// Initialise variable
		$this->folderList = array();
		$this->fileList = array();

		// Get fileset
		$eFileset = $this->manifest->fileset->files;

		// Set root folder names
		$packagePath = $this->parent->getPath('source');
		$jRootPath = JPath::clean(JPATH_ROOT);

		// Loop through all elements and get list of files and folders
		foreach ($this->manifest->fileset->files as $eFiles)
		{
			// Check if the element is files element
			$folder = (string) $eFiles->attributes()->folder;
			$target = (string) $eFiles->attributes()->target;

			//Split folder names into array to get folder names. This will
			// help in creating folders
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
					array_push($this->folderList, $folderName);
				}
			}

			// Create folder path
			$sourceFolder = empty($folder) ? $packagePath : $packagePath . '/' . $folder;
			$targetFolder = empty($target) ? $jRootPath : $jRootPath . '/' . $target;

			// Check if source folder exists
			if (!JFolder::exists($sourceFolder))
			{
				JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $sourceFolder));
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
						$folderName = $targetFolder . '/' . $eFileName;
						array_push($this->folderList, $folderName);
						$path['type'] = 'folder';
					}

					array_push($this->fileList, $path);
				}
			}
			else
			{
				$files = JFolder::files($sourceFolder);
				foreach ($files as $file)
				{
					$path['src'] = $sourceFolder . '/' . $file;
					$path['dest'] = $targetFolder . '/' . $file;

					array_push($this->fileList, $path);
				}

			}
		}
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean result of operation, true if updated, false on failure
	 *
	 * @since   11.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS . '/files/' . $this->parent->extension->element . '.xml';
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
