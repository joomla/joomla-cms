<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');
jimport('joomla.database.query');
jimport('joomla.installer.packagemanifest');


/**
 * Package installer
 *
 * @package		Joomla.Platform
 * @subpackage	Installer
 * @since		11.1
 */
class JInstallerPackage extends JAdapterInstance
{
	/**
	 * @var string method of system
	 */
	protected $route = 'install';

	public function loadLanguage($path)
	{
		$this->manifest = $this->parent->getManifest();
		$extension = 'pkg_' . strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->packagename, 'cmd'));
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
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public function install()
	{
		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		// Manifest Document Setup Section

		// Set the extensions name
		$filter	= JFilterInput::getInstance();
		$name	= (string) $this->manifest->packagename;
		$name	= $filter->clean($name, 'cmd');
		$this->set('name', $name);

		$element = 'pkg_' . $filter->clean($this->manifest->packagename, 'cmd');
		$this->set('element', $element);

		// Get the component description
		$description = (string)$this->manifest->description;
		if ($description) {
			$this->parent->set('message', JText::_($description));
		}
		else {
			$this->parent->set('message', '');
		}

		// Set the installation path
		$files = $this->manifest->files;
		$group = (string)$this->manifest->packagename;
		if (!empty($group))
		{
			// TODO: Remark this location
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'packages'.DS.implode(DS,explode('/',$group)));
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_PACK', JText::_('JLIB_INSTALLER_'. strtoupper($this->route))));
			return false;
		}

		// Filesystem Processing Section

		if ($folder = $files->attributes()->folder)
		{
			$source = $this->parent->getPath('source').DS.$folder;
		}
		else
		{
			$source = $this->parent->getPath('source');
		}

		// Install all necessary files
		if (count($this->manifest->files->children()))
		{
			foreach ($this->manifest->files->children() as $child)
			{
				$file = $source.DS.$child;
				jimport('joomla.installer.helper');
				if (is_dir($file))
				{
					// If it's actually a directory then fill it up
					$package = Array();
					$package['dir'] = $file;
					$package['type'] = JInstallerHelper::detectType($file);
				}
				else {
					// If it's an archive
					$package = JInstallerHelper::unpack($file);
				}
				$tmpInstaller = new JInstaller();
				if (!$tmpInstaller->install($package['dir']))
				{
					$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_ERROR_EXTENSION', JText::_('JLIB_INSTALLER_'. strtoupper($this->route)), basename($file)));
					return false;
				}
			}
		}
		else
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES', JText::_('JLIB_INSTALLER_'. strtoupper($this->route))));
			return false;
		}

		// Parse optional tags
		$this->parent->parseLanguages($this->manifest->languages);

		// Extension Registration

		$row = JTable::getInstance('extension');
		$eid = $row->find(Array('element'=>strtolower($this->get('element')),
						'type'=>'package'));
		if($eid) {
			$row->load($eid);
		} else {
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

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = Array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS.DS.'packages'.DS.basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_COPY_SETUP', JText::_('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES')));
			return false;
		}
		return true;
	}

	/**
	 * Updates a package
	 * The only difference between an update and a full install
	 * is how we handle the database
	 */
	public function update() {
		$this->route = 'update';
		$this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param	int		$id	The id of the package to uninstall
	 *
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	function uninstall($id)
	{
		// Initialise variables.
		$row	= null;
		$retval = true;

		$row = JTable::getInstance('extension');
		$row->load($id);


		$manifestFile = JPATH_MANIFESTS.DS.'packages' . DS . $row->get('element') .'.xml';
		$manifest = new JPackageManifest($manifestFile);

		// Set the package root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS.DS.'packages'.DS.$manifest->packagename);

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			// TODO: Fail?
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
			return false;

		}

		$xml =JFactory::getXML($manifestFile);

		// If we cannot load the xml file return false
		if (!$xml)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatability in a future version
		 * Should be 'extension', but for backward compatability we will accept 'install'.
		 */
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
			return false;
		}

		$error = false;
		foreach ($manifest->filelist as $extension)
		{
			$tmpInstaller = new JInstaller();
			$id = $this->_getExtensionID($extension->type, $extension->id, $extension->client, $extension->group);
			$client = JApplicationHelper::getClientInfo($extension->client,true);
			if ($id)
			{
				if(!$tmpInstaller->uninstall($extension->type, $id, $client->id)) {
					$error = true;
					JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)));
				}
			} else {
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'));
			}
		}

		// Remove any language files
		$this->parent->removeFiles($xml->languages);


		// clean up manifest file after we're done if there were no errors
		if (!$error) {
			JFile::delete($manifestFile);
			$row->delete();
		}
		else {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
		}

		// Return the result up the line
		return $retval;
	}

	private function _getExtensionID($type, $id, $client, $group)
	{
		$db		= $this->parent->getDbo();
		$result = $id;

		$query = new JDatabaseQuery();
		$query->select('extension_id');
		$query->from('#__extensions');
		$query->where('type = '. $db->Quote($type));
		$query->where('element = '. $db->Quote($id));

		switch($type)
		{
			case 'plugin':
				// plugins have a folder but not a client
				$query->where('folder = '. $db->Quote($group));
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
				$query->where('client_id = '. (int)$client->id);
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
	 * @return  boolean result of operation, true if updated, false on failure
	 * @since	11.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$manifestPath = JPATH_MANIFESTS.DS.'packages'. DS.$this->parent->extension->element.'.xml';
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
