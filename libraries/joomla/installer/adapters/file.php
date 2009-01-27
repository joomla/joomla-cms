<?php
/**
 * @version		$Id:plugin.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		JLibMan
 * @subpackage	Installer
 * @copyright 	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.installer.filemanifest');
jimport('joomla.base.adapterinstance');

/**
 * File installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JInstallerFile extends JAdapterInstance
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
		$manifest =& $this->parent->getManifest();
		$this->manifest =& $manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name =& $this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'string');
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $description->data());
		} else {
			$this->parent->set('message', '');
		}

		// Set the installation path
		$element =& $this->manifest->getElementByPath('files');
		$group = $this->manifest->getElementByPath('filename');
		$group = $group->data();
		if (!empty($group)) {
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'files'.DS.implode(DS,explode('/',$group)));
		} else {
			$this->parent->abort(JText::_('File').' '.JText::_('Install').': '.JText::_('No file file specified'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// If the plugin directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(JText::_('File').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
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

		// Copy all necessary files
		if ($this->parent->parseFiles($element, -1) === false) {
			// Install failed, roll back changes
			$this->parent->abort();
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
		$manifest['dest'] = JPATH_MANIFESTS.DS.'files'.DS.basename($this->parent->getPath('manifest'));
		if (!$this->parent->copyFiles(array($manifest), true)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('File').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}
		return true;
	}

	/**
	 * Custom update method
	 * @access public
	 * @return boolean True on success
	 * @since  1.5
	 */
	function update() {
		// since this is just files, an update removes old files
		// Get the extension manifest object
		$manifest =& $this->parent->getManifest();
		$this->manifest =& $manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name =& $this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'string');
		$installer = new JInstaller(); // we don't want to compromise this instance!
		$installer->uninstall('file', $name, 0);
		// ...and adds new files
		//return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	string	$id	The id of the file to uninstall
	 * @param	int		$clientId	The id of the client (unused; files are global)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall($id, $clientId)
	{
		// Initialize variables
		$row	= null;
		$retval = true;
		$manifestFile = JPATH_MANIFESTS.DS.'files' . DS . $id .'.xml';

		// Because files may not have their own folders we cannot use the standard method of finding an installation manifest
		if (file_exists($manifestFile))
		{
			$manifest = new JFileManifest($manifestFile);
			// Set the plugin root path
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'files'.DS.$manifest->filename);

			$xml =& JFactory::getXMLParser('Simple');

			// If we cannot load the xml file return null
			if (!$xml->loadFile($manifestFile)) {
				JError::raiseWarning(100, JText::_('File').' '.JText::_('Uninstall').': '.JText::_('Could not load manifest file'));
				return false;
			}

			/*
			 * Check for a valid XML root tag.
			 * @todo: Remove backwards compatability in a future version
			 * Should be 'extension', but for backward compatability we will accept 'install'.
			 */
			$root =& $xml->document;
			if ($root->name() != 'install' && $root->name() != 'extension') {
				JError::raiseWarning(100, JText::_('File').' '.JText::_('Uninstall').': '.JText::_('Invalid manifest file'));
				return false;
			}

			$this->parent->removeFiles($root->getElementByPath('files'), -1);
			JFile::delete($manifestFile);

		} else {
			JError::raiseWarning(100, 'File Uninstall: Manifest File invalid or not found');
			return false;
		}

		// TODO: Change this so it walked up the path backwards so we clobber multiple empties
		// If the folder is empty, let's delete it
		if (JFolder::exists($this->parent->getPath('extension_root'))) {
			if (is_dir($this->parent->getPath('extension_root'))) {
				$files = JFolder::files($this->parent->getPath('extension_root'));
				if (!count($files)) {
					JFolder::delete($this->parent->getPath('extension_root'));
				}
			}
		}

		return $retval;
	}

}

