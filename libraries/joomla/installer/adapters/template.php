<?php
/**
* @version		$Id:template.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Template installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerTemplate extends JObject
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$parent	Parent object [JInstaller instance]
	 * @return	void
	 * @since	1.5
	 */
	function __construct(&$parent)
	{
		$this->parent =& $parent;
	}

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function install()
	{
		// Get database connector object
		$db =& $this->parent->getDBO();
		$manifest =& $this->parent->getManifest();
		$root =& $manifest->document;

		// Get the client application target
		if ($cname = $root->attributes('client')) {
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client =& JApplicationHelper::getClientInfo($cname, true);
			if ($client === false) {
				$this->parent->abort(JText::_('Template').' '.JText::_('Install').': '.JText::_('Unknown client type').' ['.$cname.']');
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
		} else {
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		// Set the extensions name
		$name =& $root->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		// Set the template root path
		$this->parent->setPath('extension_root', $basePath.DS.'templates'.DS.strtolower(str_replace(" ", "_", $this->get('name'))));

		/*
		 * If the template directory already exists, then we will assume that the template is already
		 * installed or another template is using that directory.
		 */
		if (file_exists($this->parent->getPath('extension_root')) && !$this->parent->getOverwrite()) {
			JError::raiseWarning(100, JText::_('Template').' '.JText::_('Install').': '.JText::_('Another template is already using directory').': "'.$this->parent->getPath('extension_root').'"');
			return false;
		}

		// If the template directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(JText::_('Template').' '.JText::_('Install').': '.JText::_('Failed to create directory').' "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		// If we created the template directory and will want to remove it if we have to roll back
		// the installation, lets add it to the installation step stack
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($root->getElementByPath('files'), -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		if ($this->parent->parseFiles($root->getElementByPath('images'), -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		if ($this->parent->parseFiles($root->getElementByPath('css'), -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Parse optional tags
		$this->parent->parseFiles($root->getElementByPath('media'), $clientId);
		$this->parent->parseLanguages($root->getElementByPath('languages'));
		$this->parent->parseLanguages($root->getElementByPath('administration/languages'), 1);

		// Get the template description
		$description = & $root->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $description->data());
		} else {
			$this->parent->set('message', '' );
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Template').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$path		The template name
	 * @param	int		$clientId	The id of the client
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall( $name, $clientId )
	{
		// Initialize variables
		$retval	= true;

		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name) {
			JError::raiseWarning(100, JText::_('Template').' '.JText::_('Uninstall').': '.JText::_('Template id is empty, cannot uninstall files'));
			return false;
		}

		// Get the template root path
		$client =& JApplicationHelper::getClientInfo( $clientId );
		if (!$client) {
			JError::raiseWarning(100, JText::_('Template').' '.JText::_('Uninstall').': '.JText::_('Invalid application'));
			return false;
		}
		$this->parent->setPath('extension_root', $client->path.DS.'templates'.DS.$name);
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		$manifest =& $this->parent->getManifest();
		if (!is_a($manifest, 'JSimpleXML')) {
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JError::raiseWarning(100, 'Template Uninstall: Package manifest file invalid or not found');
			return false;
		}
		$root =& $manifest->document;

		// Remove files
		$this->parent->removeFiles($root->getElementByPath('media'), $clientId);
		$this->parent->removeFiles($root->getElementByPath('languages'));
		$this->parent->removeFiles($root->getElementByPath('administration/languages'), 1);

		// Delete the template directory
		if (JFolder::exists($this->parent->getPath('extension_root'))) {
			$retval = JFolder::delete($this->parent->getPath('extension_root'));
		} else {
			JError::raiseWarning(100, JText::_('Template').' '.JText::_('Uninstall').': '.JText::_('Directory does not exist, cannot remove files'));
			$retval = false;
		}
		return $retval;
	}
}