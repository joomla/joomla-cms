<?php
/**
 * @version		$Id:language.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Language installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerLanguage extends JObject
{
	/**
	 * Core language pack flag
	 * @access	private
	 * @var		boolean
	 */
	var $_core = false;

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
		$this->parent = &$parent;
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
		$manifest = &$this->parent->getManifest();
		$this->manifest = &$manifest->document;
		$root = &$manifest->document;

		// Get the client application target
		if ($root->attributes('client') == 'both')
		{
			$siteElement = &$root->getElementByPath('site');
			$element = &$siteElement->getElementByPath('files');
			if (!$this->_install('site', JPATH_SITE, 0, $element)) {
				return false;
			}

			$adminElement = &$root->getElementByPath('administration');
			$element = &$adminElement->getElementByPath('files');
			if (!$this->_install('administrator', JPATH_ADMINISTRATOR, 1, $element)) {
				return false;
			}

			return true;
		}
		elseif ($cname = $root->attributes('client'))
		{
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = &JApplicationHelper::getClientInfo($cname, true);
			if ($client === null) {
				$this->parent->abort(JText::_('Language').' '.JText::_('Install').': '.JText::_('Unknown client type').' ['.$cname.']');
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
			$element = &$root->getElementByPath('files');

			return $this->_install($cname, $basePath, $clientId, $element);
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
			$element = &$root->getElementByPath('files');

			return $this->_install($cname, $basePath, $clientId, $element);
		}
	}

	/**
	 *
	 */
	function _install($cname, $basePath, $clientId, &$element)
	{
		$manifest = &$this->parent->getManifest();
		$this->manifest = &$manifest->document;
		$root = &$manifest->document;

		// Get the language name
		// Set the extensions name
		$name = &$this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = &$root->getElementByPath('tag');

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if (! $tag)
		{
			$this->parent->abort(JText::_('Language').' '.JText::_('Install').': '.JText::_('NO LANGUAGE TAG?'));
			return false;
		}

		$this->set('tag', $tag->data());
		$folder = $tag->data();

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath.DS."language".DS.$this->get('tag'));

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
			$files = $element->children();
			foreach ($files as $file) {
				if ($file->attributes('file') == 'meta') {
					$this->_core = true;
					break;
				}
			}
		}

		// Either we are installing a core pack or a core pack must exist for the language we are installing.
		if (!$this->_core) {
			if (!JFile::exists($this->parent->getPath('extension_site').DS.$this->get('tag').'.xml')) {
				$this->parent->abort(JText::_('Language').' '.JText::_('Install').': '.JText::_('No core pack exists for the language').' :'.$this->get('tag'));
				return false;
			}
		}

		// If the language directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_site'))) {
				$this->parent->abort(JText::_('Language').' '.JText::_('Install').': '.JText::_('Failed to create directory').' "'.$this->parent->getPath('extension_site').'"');
				return false;
			}
		}

		/*
		 * If we created the language directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($element) === false) {
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', JPATH_SITE.DS."language".DS.'pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);
		if ($this->parent->parseFiles($root->getElementByPath('fonts')) === false) {
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		$this->parent->setOverwrite($overwrite);

		// Get the language description
		$description = & $root->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $description->data());
		} else {
			$this->parent->set('message', '');
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	string	$tag		The tag of the language to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.5
	 */
	function uninstall($tag, $clientId)
	{
		$path = trim($tag);
		if (!JFolder::exists($path)) {
			JError::raiseWarning(100, JText::_('Language').' '.JText::_('Uninstall').': '.JText::_('Language path is empty, cannot uninstall files'));
			return false;
		}

		if (!JFolder::delete($path)) {
			JError::raiseWarning(100, JText::_('Language').' '.JText::_('Uninstall').': '.JText::_('Unable to remove language directory'));
			return false;
		}
		return true;
	}
}
