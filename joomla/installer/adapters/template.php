<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.installer.extension');
jimport('joomla.base.adapterinstance');

/**
 * Template installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerTemplate extends JAdapterInstance
{
	protected $name = null;
	protected $element = null;

	/**
	 * Custom loadLanguage method
	 *
	 * @access	public
	 * @param	string	$path the path where to find language files
	 * @since	1.6
	 */
	public function loadLanguage($path)
	{
		$source = $this->parent->getPath('source');
		if (!$source) {
			$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/templates/'.$this->parent->extension->element);
		}
		$this->manifest = &$this->parent->getManifest();
		$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));
		$client = (string)$this->manifest->attributes()->client;
		$extension = "tpl_$name";
		$lang =& JFactory::getLanguage();
		$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/templates/'.$name;
			$lang->load($extension . '.sys', $source, null, false, false)
		||	$lang->load($extension . '.sys', constant('JPATH_'.strtoupper($client)), null, false, false)
		||	$lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
		||	$lang->load($extension . '.sys', constant('JPATH_'.strtoupper($client)), $lang->getDefault(), false, false);
	}
	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function install()
	{
		$xml = $this->parent->getManifest();

		// Get the client application target
		if ($cname = (string)$xml->attributes()->client)
		{
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = &JApplicationHelper::getClientInfo($cname, true);
			if ($client === false) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_UNKNOWN_CLIENT', $cname));
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		// Set the extensions name
		$name = JFilterInput::getInstance()->clean((string)$xml->name, 'cmd');

		$element = strtolower(str_replace(" ", "_", $name));
		$this->set('name', $name);
		$this->set('element',$element);

		$db = &$this->parent->getDbo();
		$db->setQuery('SELECT extension_id FROM #__extensions WHERE type="template" AND element = "'. $element .'"');
		$result = $db->loadResult();
		// TODO: Rewrite this! We shouldn't uninstall a template, we should back up the params as well
		if ($result)
		{
			// already installed, can we upgrade?
			if ($this->parent->getOverwrite() || $this->parent->getUpgrade())
			{
				// we can upgrade, so uninstall the old one
				$installer = new JInstaller(); // we don't want to compromise this instance!
				$installer->uninstall('template', $result);
			}
			else
			{
				// abort the install, no upgrade possible
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_ALREADY_INSTALLED'));
				return false;
			}
		}

		// Set the template root path
		$this->parent->setPath('extension_root', $basePath.DS.'templates'.DS.$element);

		/*
		 * If the template directory already exists, then we will assume that the template is already
		 * installed or another template is using that directory.
		 */
		if (file_exists($this->parent->getPath('extension_root')) && !$this->parent->getOverwrite())
		{
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ANOTHER_TEMPLATE_USING_DIRECTORY', $this->parent->getPath('extension_root')));
			return false;
		}

		// If the template directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_root')))
			{
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_FAILED_CREATE_DIRECTORY', $this->parent->getPath('extension_root')));
				return false;
			}
		}

		// If we created the template directory and will want to remove it if we have to roll back
		// the installation, lets add it to the installation step stack
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($xml->files, -1) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		if ($this->parent->parseFiles($xml->images, -1) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}
		if ($this->parent->parseFiles($xml->css, -1) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();
			return false;
		}

		// Parse optional tags
		$this->parent->parseFiles($xml->media, $clientId);
		$this->parent->parseLanguages($xml->languages, $clientId);

		// Get the template description
		$this->parent->set('message', JText::_((string)$xml->description));

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_SETUP'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Extension Registration
		 * ---------------------------------------------------------------------------------------------
		 */
		$row = & JTable::getInstance('extension');
		$row->name = $this->get('name');
		$row->type = 'template';
		$row->element = $this->get('name');
		$row->folder = ''; // There is no folder for templates
		$row->enabled = 1;
		$row->protected = 0;
		$row->access = 1;
		$row->client_id = $clientId;
		$row->params = $this->parent->getParams();
		$row->custom_data = ''; // custom data
		$row->manifest_cache = $this->parent->generateManifestCache();
		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ROLLBACK', $db->stderr(true)));
			return false;
		}

		//insert record in #__template_styles
		$query = 'INSERT INTO #__template_styles'.
				' (template,client_id,home,title,params)'.
				' VALUE ('.$db->Quote($row->name).','.
		$db->Quote($clientId).',0,'.
		$db->Quote(JText::_('Default')).','.
		$db->Quote($row->params).
				')';
		$db->setQuery($query);
		$db->query();
		return $row->get('extension_id');
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
	public function uninstall($id)
	{
		// Initialise variables.
		$retval	= true;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = & JTable::getInstance('extension');
		if (!$row->load((int) $id) || !strlen($row->element))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the template we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_WARNCORETEMPLATE', $row->name));
			return false;
		}
		$name = $row->element;
		$clientId = $row->client_id;


		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'));
			return false;
		}

		// Deny remove default template
		$db = &$this->parent->getDbo();
		$query = 'SELECT COUNT(*) FROM #__template_styles'.
				' WHERE home = 1 AND template = '.$db->Quote($name);
		$db->setQuery($query);
		if ($db->loadResult() != 0)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'));
			return false;
		}

		// Get the template root path
		$client = &JApplicationHelper::getClientInfo($clientId);
		if (!$client)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'));
			return false;
		}
		$this->parent->setPath('extension_root', $client->path.DS.'templates'.DS.$name);
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		$manifest = &$this->parent->getManifest();
		if (!$manifest INSTANCEOF JXMLElement)
		{
			// kill the extension entry
			$row->delete($row->extension_id);
			unset($row);
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JError::raiseWarning(100, JTEXT::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
			return false;
		}

		// Remove files
		$this->parent->removeFiles($manifest->media, $clientId);
		$this->parent->removeFiles($manifest->languages, $clientId);

		// Delete the template directory
		if (JFolder::exists($this->parent->getPath('extension_root'))) {
			$retval = JFolder::delete($this->parent->getPath('extension_root'));
		}
		else
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DIRECTORY'));
			$retval = false;
		}

		//Set menu that assigned to the template back to default template
		$query = 'UPDATE #__menu INNER JOIN #__template_styles'.
				' ON #__template_styles.id = #__menu.template_style_id'.
				' SET #__menu.template_style_id = 0'.
				' WHERE #__template_styles.template = '.$db->Quote($name).
				' AND #__template_styles.client_id = '.$db->Quote($clientId);
		$db->setQuery($query);
		$db->Query();

		$query = 'DELETE FROM #__template_styles'.
				' WHERE template = '.$db->Quote($name).
				' AND client_id = '.$db->Quote($clientId);
		$db->setQuery($query);
		$db->Query();

		$row->delete($row->extension_id);
		unset($row);
		return $retval;
	}

	/**
	 * Discover existing but uninstalled templates
	 * @return Array JExtensionTable list
	 */
	function discover()
	{
		$results = Array();
		$site_list = JFolder::folders(JPATH_SITE.DS.'templates');
		$admin_list = JFolder::folders(JPATH_ADMINISTRATOR.DS.'templates');
		$site_info = JApplicationHelper::getClientInfo('site', true);
		$admin_info = JApplicationHelper::getClientInfo('administrator', true);

		foreach ($site_list as $template)
		{
			if ($template == 'system') {
				continue;
				// ignore special system template
			}
			$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_SITE."/templates/$template/templateDetails.xml");
			$extension = &JTable::getInstance('extension');
			$extension->set('type', 'template');
			$extension->set('client_id', $site_info->id);
			$extension->set('element', $template);
			$extension->set('name', $template);
			$extension->set('state', -1);
			$extension->set('manifest_cache', serialize($manifest_details));
			$results[] = $extension;
		}
		foreach ($admin_list as $template)
		{
			if ($template == 'system') {
				continue;
				// ignore special system template
			}
			$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR."/templates/$template/templateDetails.xml");
			$extension = &JTable::getInstance('extension');
			$extension->set('type', 'template');
			$extension->set('client_id', $admin_info->id);
			$extension->set('element', $template);
			$extension->set('name', $template);
			$extension->set('state', -1);
			$extension->set('manifest_cache', serialize($manifest_details));
			$results[] = $extension;
		}
		return $results;
	}

	/**
	 * Perform an install from a discovered extension
	 */
	function discover_install()
	{
		// Templates are one of the easiest
		// If its not in the extensions table we just add it
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path.DS.'templates'.DS.$this->parent->extension->element.DS.'templateDetails.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$description = (string)$this->parent->manifest->description;
		if ($description) {
			$this->parent->set('message', JText::_($description));
		}
		else {
			$this->parent->set('message', '');
		}
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = serialize($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		$this->parent->extension->params = $this->parent->getParams();

		if ($this->parent->extension->store()) {
			return $this->parent->extension->get('extension_id');
		}
		else
		{
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_TPL_DISCOVER_STORE_DETAILS'));
			return false;
		}
	}
}