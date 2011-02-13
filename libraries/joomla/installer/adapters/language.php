<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Installer
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');

/**
 * Language installer
 *
 * @package		Joomla.Platform
 * @subpackage	Installer
 * @since		11.1
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
	 * @since	11.1
	 */
	public function install()
	{
		$source = $this->parent->getPath('source');
		if (!$source) {
			$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/language/'.$this->parent->extension->element);
		}
		$this->manifest = $this->parent->getManifest();
		$root = $this->manifest->document;

		// Get the client application target
		if ((string)$this->manifest->attributes()->client == 'both')
		{
			JError::raiseWarning(42, JText::_('JLIB_INSTALLER_ERROR_DEPRECATED_FORMAT'));
			$element = $this->manifest->site->files;
			if (!$this->_install('site', JPATH_SITE, 0, $element)) {
				return false;
			}

			$element = $this->manifest->administration->files;
			if (!$this->_install('administrator', JPATH_ADMINISTRATOR, 1, $element)) {
				return false;
			}
			// This causes an issue because we have two eid's, *sigh* nasty hacks!
			return true;
		}
		elseif ($cname = (string)$this->manifest->attributes()->client)
		{
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = JApplicationHelper::getClientInfo($cname, true);
			if ($client === null) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
			$element = $this->manifest->files;

			return $this->_install($cname, $basePath, $clientId, $element);
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
			$element = $this->manifest->files;

			return $this->_install($cname, $basePath, $clientId, $element);
		}
	}

	/**
	 * Install function that is designed to handle individual clients
	 */
	protected function _install($cname, $basePath, $clientId, &$element)
	{
		$this->manifest = $this->parent->getManifest();

		// Get the language name
		// Set the extensions name
		$name = JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = (string)$this->manifest->tag;

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if ( ! $tag)
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));
			return false;
		}

		$this->set('tag', $tag);

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.$tag);

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if ($element && count($element->children()))
		{
			$files = $element->children();
			foreach ($files as $file) {
				if ((string)$file->attributes()->file == 'meta') {
					$this->_core = true;
					break;
				}
			}
		}

		// Either we are installing a core pack or a core pack must exist for the language we are installing.
		if (!$this->_core)
		{
			if (!JFile::exists($this->parent->getPath('extension_site').DS.$this->get('tag').'.xml')) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_NO_CORE_LANGUAGE', $this->get('tag'))));
				return false;
			}
		}

		// If the language directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_FOLDER_FAILED', $this->parent->getPath('extension_site'))));
				return false;
			}
		}
		else
		{
			// look for an update function or update tag
			$updateElement = $this->manifest->update;
			// upgrade manually set
			// update function available
			// update tag detected
			if ($this->parent->getUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JXMLElement')) {
				return $this->update(); // transfer control to the update function
			}
			else if (!$this->parent->getOverwrite())
			{
				// overwrite is set
				// we didn't have overwrite set, find an update function or find an update tag so lets call it safe
				if (file_exists($this->parent->getPath('extension_site'))) { // if the site exists say that
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_site'))));
				}
				else { // if the admin exists say that
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_administrator'))));
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

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->media);

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.'pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);
		if ($this->parent->parseFiles($this->manifest->fonts) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		$this->parent->setOverwrite($overwrite);

		// Get the language description
		$description = (string)$this->manifest->description;
		if ($description) {
			$this->parent->set('message', JText::_($description));
		}
		else {
			$this->parent->set('message', '');
		}

		// Add an entry to the extension table with a whole heap of defaults
		$row = JTable::getInstance('extension');
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
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));
			return false;
		}

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
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
		$xml = $this->parent->getManifest();

		$this->manifest	= $xml;

		$cname		= $xml->attributes()->client;

		// Attempt to map the client to a base path
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cname, true);
		if ($client === null || (empty($cname) && $cname !== 0))
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));
			return false;
		}
		$basePath = $client->path;
		$clientId = $client->id;

		// Get the language name
		// Set the extensions name
		$name = (string)$this->manifest->name;
		$name = JFilterInput::getInstance()->clean($name, 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = (string)$xml->tag;

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if (!$tag)
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));
			return false;
		}

		$this->set('tag', $tag);
		$folder = $tag;

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.$this->get('tag'));

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if (count($xml->files->children()))
		{
			foreach ($xml->files->children() as $file)
			{
				if ((string)$file->attributes()->file == 'meta')
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
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_NO_CORE_LANGUAGE', $this->get('tag'))));
				return false;
			}
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($xml->files) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Parse optional tags
		$this->parent->parseMedia($xml->media);

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath.DS.'language'.DS.'pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);
		if ($this->parent->parseFiles($xml->fonts) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		$this->parent->setOverwrite($overwrite);

		// Get the language description and set it as message
		$this->parent->set('message', (string)$xml->description);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('tag'),
								'type'=>'language',
								'client_id'=>$clientId));
		if ($uid)
		{
			$update->delete($uid);
		}

		// Update an entry to the extension table
		$row = JTable::getInstance('extension');
		$eid = $row->find(Array('element'=>strtolower($this->get('tag')),
						'type'=>'language', 'client_id'=>$clientId));
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
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));
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
	 * @since	11.1
	 */
	public function uninstall($eid)
	{
		// load up the extension details
		$extension = JTable::getInstance('extension');
		$extension->load($eid);
		// grab a copy of the client details
		$client = JApplicationHelper::getClientInfo($extension->get('client_id'));

		// check the element isn't blank to prevent nuking the languages directory...just in case
		$element = $extension->get('element');
		if (empty($element))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_ELEMENT_EMPTY'));
			return false;
		}

		// verify that it's not the default language for that client
		$params = JComponentHelper::getParams('com_languages');
		if ($params->get($client->name)==$element) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DEFAULT'));
			return false;
		}

		// construct the path from the client, the language and the extension element name
		$path = $client->path.DS.'language'.DS.$element;

		// Get the package manifest object and remove media
		$this->parent->setPath('source', $path);
		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();
		$this->parent->removeFiles($this->manifest->media);

		// check it exists
		if (!JFolder::exists($path))
		{
			// if the folder doesn't exist lets just nuke the row as well and presume the user killed it for us
			$extension->delete();
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_PATH_EMPTY'));
			return false;
		}

		if (!JFolder::delete($path))
		{
			// if deleting failed we'll leave the extension entry in tact just in case
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DIRECTORY'));
			return false;
		}

		// Remove the extension table entry
		$extension->delete();

		// Setting the language of users which have this language as the default language
		$db = JFactory::getDbo();
		$query=$db->getQuery(true);
		$query->from('#__users');
		$query->select('*');
		$db->setQuery($query);
		$users = $db->loadObjectList();
		if($client->name == 'administrator') {
			$param_name = 'admin_language';
		} else {
			$param_name = 'language';
		}

		$count = 0;
		foreach ($users as $user) {
			$registry = new JRegistry;
			$registry->loadJSON($user->params);
			if ($registry->get($param_name)==$element) {
				$registry->set($param_name,'');
				$query=$db->getQuery(true);
				$query->update('#__users');
				$query->set('params='.$db->quote($registry));
				$query->where('id='.(int)$user->id);
				$db->setQuery($query);
				$db->query();
				$count = $count + 1;
			}
		}
		if (!empty($count)) {
			JError::raiseNotice(500, JText::plural('JLIB_INSTALLER_NOTICE_LANG_RESET_USERS', $count));
		}

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
				$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_SITE.DS.'language'.DS.$language.DS.$language.'.xml');
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 0);
				$extension->set('element', $language);
				$extension->set('name', $language);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$results[] = $extension;
			}
		}
		foreach ($admin_languages as $language)
		{
			if (file_exists(JPATH_ADMINISTRATOR.DS.'language'.DS.$language.DS.$language.'.xml'))
			{
				$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR.DS.'language'.DS.$language.DS.$language.'.xml');
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 1);
				$extension->set('element', $language);
				$extension->set('name', $language);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$results[] = $extension;
			}
		}
		return $results;
	}

	/**
	 * Custom discover install method
	 * Basically updates the manifest cache and leaves everything alone
	 */
	public function discover_install()
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
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		//$this->parent->extension->params = $this->parent->getParams();
		try {
			$this->parent->extension->store();
		}
		catch(JException $e)
		{
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_LANG_DISCOVER_STORE_DETAILS'));
			return false;
		}
		return $this->parent->extension->get('extension_id');
	}

	/**
	 * Refreshes the extension table cache
	 * @return  boolean result of operation, true if updated, false on failure
	 * @since	11.1
	 */
	public function refreshManifestCache()
	{
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . DS . 'language'. DS . $this->parent->extension->element . DS . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		if ($this->parent->extension->store()) {
			return true;
		}
		else {
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_MOD_REFRESH_MANIFEST_CACHE'));

			return false;
		}
	}
}
