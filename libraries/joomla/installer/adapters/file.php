<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.installer.filemanifest');
jimport('joomla.base.adapterinstance');

/**
 * File installer
 *
 * @package		Joomla.Platform
 * @subpackage	Installer
 * @since		11.1
 */
class JInstallerFile extends JAdapterInstance
{
	private $route = 'install';

	/**
	 * Custom loadLanguage method
	 *
	 * @access	public
	 * @param	string	$path the path where to find language files
	 * @since	11.1
	 */
	public function loadLanguage($path)
	{
		$this->manifest = $this->parent->getManifest();
		$extension = 'files_'. str_replace('files_','',strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd')));
		$lang = JFactory::getLanguage();
		$source = $path;
			$lang->load($extension . '.sys', $source, null, false, false)
		||	$lang->load($extension . '.sys', JPATH_SITE, null, false, false)
		||	$lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
		||	$lang->load($extension . '.sys', JPATH_SITE, $lang->getDefault(), false, false);
	}

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public function install()
	{
		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name = JFilterInput::getInstance()->clean((string)$this->manifest->name, 'string');
		$this->set('name', $name);

		// Set element
		$manifestPath = JPath::clean($this->parent->getPath('manifest'));
		$element = explode('/',$manifestPath);
		$element = $element[count($element) - 1];
		$element = preg_replace('/\.xml/', '', $element);
		$this->set('element', $element);

		// Get the component description
		$description = (string)$this->manifest->description;
		if ($description) {
			$this->parent->set('message', JText::_($description));
		} else {
			$this->parent->set('message', '');
		}


		//Check if the extension by the same name is already installed
		if ($this->extensionExistsInSystem($element))
		{
			// Package with same name already exists
			if(!$this->parent->getOverwrite())
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


		//Populate File and Folder List to copy
		$this->populateFilesAndFolderList();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */


		//Now that we have folder list, lets start creating them
		foreach ($this->folderList as $folder)
		{
			if (!JFolder::exists($folder))
			{

				if (!$created = JFolder::create($folder))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $folder));
					// if installation fails, rollback
					$this->parent->abort();
					return false;
				}

				/*
				 * Since we created a directory and will want to remove it if we have to roll back
				 * the installation due to some errors, lets add it to the installation step stack
				 */
				if ($created) {
					$this->parent->pushStep(array ('type' => 'folder', 'path' => $folder));
				}
			}

		}

		//Now that we have file list , lets start copying them
		$this->parent->copyFiles($this->fileList);

		// Parse optional tags
		$this->parent->parseLanguages($this->manifest->languages);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Get a database connector object
		$db = $this->parent->getDbo();

		// Check to see if a module by the same name is already installed
		// If it is, then update the table because if the files aren't there
		// we can assume that it was (badly) uninstalled
		// If it isn't, add an entry to extensions
		$query = 'SELECT `extension_id`' .
				' FROM `#__extensions` ' .
				' WHERE type = '. $db->Quote('file') .' AND element = '.$db->Quote($element);
		$db->setQuery($query);
		try {
			$db->Query();
		}
		catch(JException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_'.$this->route), $db->stderr(true)));
			return false;
		}
		$id = $db->loadResult();
		$row = JTable::getInstance('extension');

		if ($id)
		{
			// load the entry and update the manifest_cache
			$row->load($id);
			$row->set('name', $this->get('name')); // update name
			$row->manifest_cache = $this->parent->generateManifestCache(); // update manifest
			if (!$row->store()) {
				// Install failed, roll back changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_'.$this->route), $db->stderr(true)));
				return false;
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults
			$row->set('name', $this->get('name'));
			$row->set('type', 'file');
			$row->set('element', $this->get('element'));
			$row->set('folder', ''); // There is no folder for files so leave it blank
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', 0);
			$row->set('params', '');
			$row->set('system_data', '');
			$row->set('manifest_cache', '');

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_ROLLBACK', $db->stderr(true)));
				return false;
			}

			// set the insert id
			$row->set('extension_id', $db->insertid());

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array ('type' => 'extension', 'extension_id' => $row->extension_id));
		}


		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = Array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS.DS.'files'.DS.basename($this->parent->getPath('manifest'));
		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_COPY_SETUP'));
			return false;
		}

                // Clobber any possible pending updates
                $update = JTable::getInstance('update');
                $uid = $update->find(
                        array(
                                'element'       => $this->get('element'),
                                'type'          => 'file',
                                'client_id'     => '',
                                'folder'        => ''
                        )
                );

                if ($uid) {
                        $update->delete($uid);
                }


		return $row->get('extension_id');
	}

	/**
	 * Custom update method
	 * @access public
	 * @return boolean True on success
	 * @since  11.1
	 */
	public function update()
	{
		// set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);
		$this->route = 'update';

		// ...and adds new files
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	string	$id	The id of the file to uninstall
	 * @param	int		$clientId	The id of the client (unused; files are global)
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$row	= JTable::getInstance('extension');
		if(!$row->load($id)) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_ENTRY'));
			return false;
		}

		$retval = true;
		$manifestFile = JPATH_MANIFESTS.DS.'files' . DS . $row->element .'.xml';

		// Because files may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			// Set the plugin root path
			$this->parent->setPath('extension_root', JPATH_ROOT); //.DS.'files'.DS.$manifest->filename);

			$xml =JFactory::getXML($manifestFile);

			// If we cannot load the xml file return null
			if( ! $xml) {
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_MANIFEST'));
				return false;
			}

			/*
			 * Check for a valid XML root tag.
			 */
			if ($xml->getName() != 'extension') {
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_MANIFEST'));
				return false;
			}

			$this->manifest = $xml;

			// Set root folder names
			$packagePath = $this->parent->getPath('source');
			$jRootPath = JPath::clean(JPATH_ROOT);

			// loop through all elements and get list of files and folders
			foreach ($xml->fileset->files as $eFiles)
			{
					$folder = (string)$eFiles->attributes()->folder;
					$target = (string)$eFiles->attributes()->target;
					//Create folder path
					if(empty($target))
					{
						$targetFolder = JPATH_ROOT;
					}
					else
					{
						$targetFolder = JPATH_ROOT.DS.$target;
					}

					$folderList = array();
					// Check if all children exists
					if (count($eFiles->children()) > 0)
					{
						// loop through all filenames elements
						foreach ($eFiles->children() as $eFileName)
						{
							if ($eFileName->getName() == 'folder') {
								$folderList[] = $targetFolder.DS.$eFileName;

							} else {
								$fileName = $targetFolder.DS.$eFileName;
								JFile::delete($fileName);
							}
						}
					}

					// Delete any folders that don't have any content in them
					foreach($folderList as $folder)
					{
						$files = JFolder::files($folder);
						if(!count($files)) {
							JFolder::delete($folder);
						}
					}
			}

			JFile::delete($manifestFile);

		} else {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
			// delete the row because its broken
			$row->delete();
			return false;
		}

		$this->parent->removeFiles($xml->languages);

		$row->delete();

		return $retval;
	}

	/**
	 * function used to check if extension is already installed
	 *
	 * @access	private
	 * @param	string	$element The element name of the extension to install
	 * @return	boolean	True if extension exists
	 * @since	11.1
	 */

	private function extensionExistsInSystem($extension = null)
	{

		// Get a database connector object
		$db = $this->parent->getDBO();

		$query = 'SELECT `extension_id`' .
				' FROM `#__extensions`' .
				' WHERE element = '.$db->Quote($extension) .
				' AND type = "file"';

		$db->setQuery($query);

		try {
			$db->Query();
		} catch(JException $e) {
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', $db->stderr(true)));
			return false;
		}
		$id = $db->loadResult();

		if (empty($id))
		return false;

		return true;

	}

	/**
	 * function used to populate files and folder list
	 *
	 * @access	private
	 * @return	boolean	none
	 * @since	11.1
	 */
	private function populateFilesAndFolderList()
	{

		// Initialise variable
		$this->folderList = array();
		$this->fileList = array();

		// Get fileset
		$eFileset = $this->manifest->fileset->files;

		// Set root folder names
		$packagePath = $this->parent->getPath('source');
		$jRootPath = JPath::clean(JPATH_ROOT);

		// loop through all elements and get list of files and folders
		foreach ($this->manifest->fileset->files as $eFiles)
		{
			// Check if the element is files element
			$folder = (string)$eFiles->attributes()->folder;
			$target = (string)$eFiles->attributes()->target;

			//Split folder names into array to get folder names. This will
			// help in creating folders
			$arrList = preg_split("#/|\\/#", $target);

			$folderName = $jRootPath;
			foreach ($arrList as $dir)
			{
				if(empty($dir)) continue ;

				$folderName .= DS.$dir;
				// Check if folder exists, if not then add to the array for folder creation
				if (!JFolder::exists($folderName)) {
					array_push($this->folderList, $folderName);
				}
			}


			//Create folder path
			$sourceFolder = empty($folder)?$packagePath:$packagePath.DS.$folder;
			$targetFolder = empty($target)?$jRootPath:$jRootPath.DS.$target;

			//Check if source folder exists
			if (! JFolder::exists($sourceFolder)) {
				JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $sourceFolder));
				// if installation fails, rollback
				$this->parent->abort();
				return false;
			}

			// Check if all children exists
			if (count($eFiles->children()))
			{
				// loop through all filenames elements
				foreach ($eFiles->children() as $eFileName)
				{
					$path['src'] = $sourceFolder.DS.$eFileName;
					$path['dest'] = $targetFolder.DS.$eFileName;
					$path['type'] = 'file';
					if ($eFileName->getName() == 'folder') {
						$folderName = $targetFolder.DS.$eFileName;
						array_push($this->folderList, $folderName);
						$path['type'] = 'folder';
					}

					array_push($this->fileList, $path);
				}
			} else {
				$files = JFolder::files($sourceFolder);
				foreach ($files as $file) {
					$path['src'] = $sourceFolder.DS.$file;
					$path['dest'] = $targetFolder.DS.$file;

					array_push($this->fileList, $path);
				}

			}
		}
	}

	/**
	 * Refreshes the extension table cache
	 * @return  boolean result of operation, true if updated, false on failure
	 * @since	11.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS.DS.'files'. DS.$this->parent->extension->element.'.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try {
			return $this->parent->extension->store();
		}
		catch(JException $e) {
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_PACK_REFRESH_MANIFEST_CACHE'));
			return false;
		}
	}
}
