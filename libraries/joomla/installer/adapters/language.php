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
 * Language installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerLanguage extends JAdapterInstance
{
	/**
	 * Core language pack flag
	 * @access	private
	 * @var		boolean
	 */
	protected $_core = false;

	/**
	 * Custom install method
	 * Note: This behaves badly due to hacks made in the middle of 1.5.x to add
	 * the ability to install multiple distinct packs in one install. The
	 * preferred method is to use a package to install multiple language packs.
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function install()
	{
		$manifest = &$this->parent->getManifest();
		$this->manifest = &$manifest->document;
		$root = &$manifest->document;

		// Get the client application target
		if ($root->attributes('client') == 'both')
		{
			JError::raiseWarning(42, JText::_('Instr_Error_Deprecated_format'));
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
			// This causes an issue because we have two eid's, *sigh* nasty hacks!
			return true;
		}
		elseif ($cname = $root->attributes('client'))
		{
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = &JApplicationHelper::getClientInfo($cname, true);
			if ($client === null) {
				$this->parent->abort(JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_Unknown_client_type', $cname)));
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
	 * Install function that is designed to handle individual clients
	 */
	protected function _install($cname, $basePath, $clientId, &$element)
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
		if (!$tag)
		{
			$this->parent->abort(JText::sprintf('Instr_Abort', JText::_('Instr_Error_No_Language_Tag')));
			return false;
		}

		$this->set('tag', $tag->data());
		$folder = $tag->data();

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.$this->get('tag'));

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if ($element INSTANCEOF JSimpleXMLElement && count($element->children()))
		{
			$files = $element->children();
			foreach ($files as $file) {
				if ($file->attributes('file') == 'meta') {
					$this->_core = true;
					break;
				}
			}
		}

		// Either we are installing a core pack or a core pack must exist for the language we are installing.
		if (!$this->_core)
		{
			if (!JFile::exists($this->parent->getPath('extension_site').DS.$this->get('tag').'.xml')) {
				$this->parent->abort(JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_No_core_language', $this->get('tag'))));
				return false;
			}
		}

		// If the language directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				$this->parent->abort(JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_Create_folder_failed', $this->parent->getPath('extension_site'))));
				return false;
			}
		}
		else
		{
			// look for an update function or update tag
			$updateElement = $this->manifest->getElementByPath('update');
			// upgrade manually set
			// update function available
			// update tag detected
			if ($this->parent->getUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JSimpleXMLElement')) {
				return $this->update(); // transfer control to the update function
			}
			else if (!$this->parent->getOverwrite())
			{
				// overwrite is set
				// we didn't have overwrite set, find an update function or find an update tag so lets call it safe
				if (file_exists($this->parent->getPath('extension_site'))) { // if the site exists say that
					JError::raiseWarning(1, JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_Folder_in_use', $this->parent->getPath('extension_site'))));
				}
				else { // if the admin exists say that
					JError::raiseWarning(1, JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_Folder_in_use', $this->parent->getPath('extension_administrator'))));
				}
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
		if ($this->parent->parseFiles($element) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.'pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);
		if ($this->parent->parseFiles($root->getElementByPath('fonts')) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		$this->parent->setOverwrite($overwrite);

		// Get the language description
		$description = & $root->getElementByPath('description');
		if ($description INSTANCEOF JSimpleXMLElement) {
			$this->parent->set('message', $description->data());
		}
		else {
			$this->parent->set('message', '');
		}

		// Add an entry to the extension table with a whole heap of defaults
		$row = & JTable::getInstance('extension');
		$row->set('name', $this->get('name'));
		$row->set('type', 'language');
		$row->set('element', $this->get('tag'));
		$row->set('folder', ''); // There is no folder for languages
		$row->set('enabled', 1);
		$row->set('protected', 0);
		$row->set('access', 0);
		$row->set('client_id', $clientId);
		$row->set('params', $this->parent->getParams());
		$row->set('manifest_cache', $this->parent->generateManifestCache());

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('Instr_Abort', $db->getErrorMsg()));
			return false;
		}

		// Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('tag'),
								'type'=>'language',
								'client_id'=>'',
								'folder'=>''));
		if ($uid) {
			$update->delete($uid);
		}

		return $row->get('extension_id');
	}

	/**
	 * Custom update method
	 *
	 * @return boolean True on success, false on failure
	 * @since 1.6
	 */
	public function update()
	{
		$manifest	= &$this->parent->getManifest();
		$this->manifest = &$manifest->document;
		$root		= &$manifest->document;
		$cname		= $root->attributes('client');

		// Attempt to map the client to a base path
		jimport('joomla.application.helper');
		$client = &JApplicationHelper::getClientInfo($cname, true);
		if ($client === null || (empty($cname) && $cname !== 0))
		{
			$this->parent->abort(JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_Unknown_client_type', $cname)));
			return false;
		}
		$basePath = $client->path;
		$clientId = $client->id;
		$element = &$root->getElementByPath('files');

		// Get the language name
		// Set the extensions name
		$name = &$this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = &$root->getElementByPath('tag');

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if (!$tag)
		{
			$this->parent->abort(JText::sprintf('Instr_Abort', JText::_('Instr_Error_No_Language_Tag')));
			return false;
		}

		$this->set('tag', $tag->data());
		$folder = $tag->data();

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.$this->get('tag'));

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if ($element INSTANCEOF JSimpleXMLElement && count($element->children()))
		{
			$files = $element->children();
			foreach ($files as $file)
			{
				if ($file->attributes('file') == 'meta')
				{
					$this->_core = true;
					break;
				}
			}
		}

		// Either we are installing a core pack or a core pack must exist for the language we are installing.
		if (!$this->_core)
		{
			if (!JFile::exists($this->parent->getPath('extension_site').DS.$this->get('tag').'.xml'))
			{
				$this->parent->abort(JText::sprintf('Instr_Abort', JText::sprintf('Instr_Error_No_core_language', $this->get('tag'))));
				return false;
			}
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($element) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.'pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);
		if ($this->parent->parseFiles($root->getElementByPath('fonts')) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		$this->parent->setOverwrite($overwrite);

		// Get the language description
		$description = & $root->getElementByPath('description');
		if ($description INSTANCEOF JSimpleXMLElement) {
			$this->parent->set('message', $description->data());
		}
		else {
			$this->parent->set('message', '');
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('tag'),
								'type'=>'language',
								'client_id'=>$clientId));
		if ($uid)
		{
			$update->delete($uid);
		}

		// Update an entry to the extension table
		$row = & JTable::getInstance('extension');
		$eid = $row->find(Array('element'=>strtolower($this->get('tag')),
						'type'=>'language'));
		if ($eid) {
			$row->load($eid);
		}
		else
		{
			// set the defaults
			$row->set('folder', ''); // There is no folder for language
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', $clientId);
			$row->set('params', $this->parent->getParams());
		}
		$row->set('name', $this->get('name'));
		$row->set('type', 'language');
		$row->set('element', $this->get('tag'));
		$row->set('manifest_cache', $this->parent->generateManifestCache());

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('Instr_Abort', $db->getErrorMsg()));
			return false;
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight'))
		{
			$this->parent->manifestClass->postflight('update', $this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}

		return $row->get('extension_id');
	}

	/**
	 * Custom uninstall method
	 *
	 * @param	string	$tag		The tag of the language to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.5
	 */
	public function uninstall($eid)
	{
		// load up the extension details
		$extension = JTable::getInstance('extension');
		$extension->load($eid);

		// check the element isn't blank to prevent nuking the languages directory...just in case
		$element = $extension->get('element');
		if (empty($element))
		{
			JError::raiseWarning(100, JText::_('Language').' '.JText::_('Uninstall').': '.JText::_('Element is empty, cannot uninstall files'));
			return false;
		}

		// grab a copy of the client details
		$client = JApplicationHelper::getClientInfo($extension->get('client_id'));
		// construct the path from the client, the language and the extension element name
		$path = $client->path.DS.'language'.DS.$element;

		// check it exists
		if (!JFolder::exists($path))
		{
			JError::raiseWarning(100, JText::_('Language').' '.JText::_('Uninstall').': '.JText::_('Language path is empty, cannot uninstall files'));
			return false;
		}

		if (!JFolder::delete($path))
		{
			JError::raiseWarning(100, JText::_('Language').' '.JText::_('Uninstall').': '.JText::_('Unable to remove language directory'));
			return false;
		}

		// Remove the extension table entry
		$extension->delete();

		// All done!
		return true;
	}

	/**
	 * Custom discover method
	 * Finds language files
	 */
	public function discover()
	{
		$results = Array();
		$site_languages = JFolder::folders(JPATH_SITE.DS.'language');
		$admin_languages = JFolder::folders(JPATH_ADMINISTRATOR.DS.'language');
		foreach ($site_languages as $language)
		{
			if (file_exists(JPATH_SITE.DS.'language'.DS.$language.DS.$language.'.xml'))
			{
				$extension = &JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 0);
				$extension->set('element', $language);
				$extension->set('name', $language);
				$extension->set('state', -1);
				$results[] = $extension;
			}
		}
		foreach ($admin_languages as $language)
		{
			if (file_exists(JPATH_ADMINISTRATOR.DS.'language'.DS.$language.DS.$language.'.xml'))
			{
				$extension = &JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 1);
				$extension->set('element', $language);
				$extension->set('name', $language);
				$extension->set('state', -1);
				$results[] = $extension;
			}
		}
		return $results;
	}

	/**
	 * Custom discover install method
	 * Basically updates the manifest cache and leaves everything alone
	 */
	function discover_install()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = $this->parent->extension->element;
		$manifestPath = $client->path . DS . 'language'. DS . $short_element . DS . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('source', $client->path . DS . 'language'. DS . $short_element);
		$this->parent->setPath('extension_root', $this->parent->getPath('source'));
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = serialize($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		//$this->parent->extension->params = $this->parent->getParams();
		try {
			$this->parent->extension->store();
		}
		catch(JException $e)
		{
			JError::raiseWarning(101, JText::_('Language').' '.JText::_('Discover Install').': '.JText::_('Failed to store extension details'));
			return false;
		}
		return $this->parent->extension->get('extension_id');
	}
}
