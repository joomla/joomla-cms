<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * Package installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JInstallerPackage extends JAdapterInstance
{

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function install()
	{
		// Get the extension manifest object
		$manifest = &$this->parent->getManifest();
		$this->manifest = &$manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name = &$this->manifest->getElementByPath('packagename');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $description->data());
		}
		else {
			$this->parent->set('message', '');
		}

		// Set the installation path
		$element = &$this->manifest->getElementByPath('files');
		$group = $this->manifest->getElementByPath('packagename');
		$group = $group->data();
		if (!empty($group))
		{
			// TODO: Remark this location
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'libraries'.DS.implode(DS,explode('/',$group)));
		}
		else
		{
			$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.JText::_('No package file specified'));
			return false;
		}

		/**
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
				$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		/*
		 * If we created the plugin directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		if ($folder = $element->attributes('folder')) {
			$source = $this->parent->getPath('source').DS.$folder;
		}
		else {
			$source = $this->parent->getPath('source');
		}

		// Install all necessary files
		if (is_a($element, 'JSimpleXMLElement') && count($element->children()))
		{
			foreach ($element->children() as $child)
			{
				$file = $source . DS . $child->data();
				jimport('joomla.installer.helper');
				if (is_dir($file))
				{
					// if its actually a directory then fill it up
					$package = Array();
					$package['dir'] = $file;
					$package['type'] = JInstallerHelper::detectType($file);
				}
				else { // if its an archive
					$package = JInstallerHelper::unpack($file);
				}
				$tmpInstaller = new JInstaller();
				if (!$tmpInstaller->install($package['dir']))
				{
					$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.JText::_('There was an error installing an extension:') . basename($file));
					return false;
				}
			}
		}
		else
		{
			$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.JText::_('There were no files to install!').print_r($element,1));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Extension Registration
		 * ---------------------------------------------------------------------------------------------
		 */
		$row = & JTable::getInstance('extension');
		$row->name = $this->get('name');
		$row->type = 'package';
		$row->element = $this->get('element');
		$row->folder = ''; // There is no folder for modules
		$row->enabled = 1;
		$row->protected = 0;
		$row->access = 1;
		$row->client_id = 0;
		$row->params = $this->parent->getParams();
		$row->custom_data = ''; // custom data
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.$db->stderr(true));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = Array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS.DS.'packages'.DS.basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Package').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$id	The id of the package to uninstall
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall($id)
	{
		// Initialise variables.
		$row	= null;
		$retval = true;

		$row = & JTable::getInstance('extension');
		$row->load($id);

		$manifestFile = JPATH_MANIFESTS.DS.'packages' . DS . $row->get('element') .'.xml';
		$manifest = new JPackageManifest($manifestFile);

		// Set the plugin root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS.DS.'packages'.DS.$manifest->packagename);

		// Because libraries may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			$xml = &JFactory::getXMLParser('Simple');

			// If we cannot load the xml file return null
			if (!$xml->loadFile($manifestFile))
			{
				JError::raiseWarning(100, JText::_('Package').' '.JText::_('Uninstall').': '.JText::_('Could not load manifest file'));
				return false;
			}

			/*
			 * Check for a valid XML root tag.
			 * @todo: Remove backwards compatability in a future version
			 * Should be 'extension', but for backward compatability we will accept 'install'.
			 */
			$root = &$xml->document;
			if ($root->name() != 'install' && $root->name() != 'extension')
			{
				JError::raiseWarning(100, JText::_('Package').' '.JText::_('Uninstall').': '.JText::_('Invalid manifest file'));
				return false;
			}

			$error = false;
			foreach ($manifest->filelist as $extension)
			{
				$tmpInstaller = new JInstaller();
				$id = $this->_getExtensionID($extension->type, $extension->id, $extension->client, $extension->group);
				$client = JApplicationHelper::getClientInfo($extension->client,true);
				if (!$tmpInstaller->uninstall($extension->type, $id, $client->id))
				{
					$error = true;
					JError::raiseWarning(100, JText::_('Package').' '.JText::_('Uninstall').': '.
//							JText::_('There was an error removing an extension!') . ' ' .
							JText::_('This extension may have already been uninstalled or might not have been uninstall properly') .': ' .
							basename($extension->filename));
					//$this->parent->abort(JText::_('Package').' '.JText::_('Uninstall').': '.JText::_('There was an error removing an extension, try reinstalling:') . basename($extension->filename));
					//return false;
				}
			}
			// clean up manifest file after we're done if there were no errors
			if (!$error) {
				JFile::delete($manifestFile);
			}
			else {
				JError::raiseWarning(100, JText::_('Package'). ' ' . JText::_('Uninstall'). ': '.
					JText::_('Errors were detected, manifest file not removed!'));
			}
		}
		else
		{
			JError::raiseWarning(100, JText::_('Package').' '.JText::_('Uninstall').': '.
				JText::_('Manifest File invalid or not found') . $id);
			return false;
		}

		return $retval;
	}

	function _getExtensionID($type, $id, $client, $group)
	{
		$db		= &$this->parent->getDbo();
		$result = $id;

		switch($type)
		{
			case 'plugin':
				$db->setQuery("SELECT extension_id FROM #__extensions WHERE `type` = 'plugin' AND `folder` = '$group' AND `element` = '$id'");
				$result = $db->loadResult();
				break;

			case 'component':
				$db->setQuery("SELECT extension_id FROM #__extensions WHERE `type` = 'component' AND `element` = '$id'");
				$result = $db->loadResult();
				break;

			case 'module':
				$db->setQuery("SELECT id FROM #__modules WHERE module = '$id' and client_id = '$client'");
				$result = $db->loadResult();
				break;

			case 'language':
				// A language is a complex beast
				// its actually a path!
				$clientInfo = &JApplicationHelper::getClientInfo($this->_state->get('filter.client'));
				$client = $clientInfo->name;
				$langBDir = JLanguage::getLanguagePath($clientInfo->path);
				$result = $langBDir . DS . $id;
				break;
		}

		// note: for templates, libraries and packages their unique name is their key
		// this means they come out the same way they came in
		return $result;
	}
}
