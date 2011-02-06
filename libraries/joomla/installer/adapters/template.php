<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Installer
 */

defined('JPATH_PLATFORM') or die;

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
	protected $route = 'install';

	/**
	 * Custom loadLanguage method
	 *
	 * @param	string	$path the path where to find language files
	 *
	 * @since	1.6
	 */
	public function loadLanguage($path=null)
	{
		$source = $this->parent->getPath('source');

		if (!$source) {
			$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/templates/'.$this->parent->extension->element);
		}

		$clientId = isset($this->parent->extension) ? $this->parent->extension->client_id : 0;
		$this->manifest = $this->parent->getManifest();
		$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));
		$client = (string)$this->manifest->attributes()->client;

		// Load administrator language if not set.
		if (!$client) {
			$client = 'ADMINISTRATOR';
		}

		$extension = "tpl_$name";
		$lang = JFactory::getLanguage();
		$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/templates/'.$name;
			$lang->load($extension . '.sys', $source, null, false, false)
		||	$lang->load($extension . '.sys', constant('JPATH_'.strtoupper($client)), null, false, false)
		||	$lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
		||	$lang->load($extension . '.sys', constant('JPATH_'.strtoupper($client)), $lang->getDefault(), false, false);
	}

	/**
	 * Custom install method
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function install()
	{
		$lang = JFactory::getLanguage();
		$xml = $this->parent->getManifest();

		// Get the client application target
		if ($cname = (string)$xml->attributes()->client) {
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = JApplicationHelper::getClientInfo($cname, true);
			if ($client === false) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_UNKNOWN_CLIENT', $cname));
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
		}
		else {
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

		$db = $this->parent->getDbo();
		$db->setQuery('SELECT extension_id FROM #__extensions WHERE type="template" AND element = "'. $element .'"');
		$id = $db->loadResult();

		// Set the template root path
		$this->parent->setPath('extension_root', $basePath.DS.'templates'.DS.$element);


		// if its on the fs...
		if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->getOverwrite() || $this->parent->getUpgrade()))
		{
			$updateElement = $xml->update;
			// upgrade manually set
			// update function available
			// update tag detected
			if ($this->parent->getUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JXMLElement'))
			{
				// force these one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);
				if ($id) { // if there is a matching extension mark this as an update; semantics really
					$this->route = 'update';
				}
			}
			else if (!$this->parent->getOverwrite())
			{
				// overwrite is set
				// we didn't have overwrite set, find an udpate function or find an update tag so lets call it safe
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_DIRECTORY', JText::_('JLIB_INSTALLER_'.$this->route), $this->parent->getPath('extension_root')));
				return false;
			}
		}

		/*
		 * If the template directory already exists, then we will assume that the template is already
		 * installed or another template is using that directory.
		 */
		if (file_exists($this->parent->getPath('extension_root')) && !$this->parent->getOverwrite()) {
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ANOTHER_TEMPLATE_USING_DIRECTORY', $this->parent->getPath('extension_root')));
			return false;
		}

		// If the template directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
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
		if ($this->parent->parseFiles($xml->files, -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		if ($this->parent->parseFiles($xml->images, -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		if ($this->parent->parseFiles($xml->css, -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		// Parse optional tags
		$this->parent->parseMedia($xml->media);
		$this->parent->parseLanguages($xml->languages, $clientId);

		// Get the template description
		$this->parent->set('message', JText::_((string)$xml->description));

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_SETUP'));

			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Extension Registration
		 * ---------------------------------------------------------------------------------------------
		 */
		$row = JTable::getInstance('extension');

		if($this->route == 'update' && $id)
		{
			$row->load($id);
		}
		else
		{
			$row->type = 'template';
			$row->element = $this->get('element');
			$row->folder = ''; // There is no folder for templates
			$row->enabled = 1;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = $clientId;
			$row->params = $this->parent->getParams();
			$row->custom_data = ''; // custom data
		}
		$row->name = $this->get('name'); // name might change in an update
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store()) {
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ROLLBACK', $db->stderr(true)));

			return false;
		}

		if($this->route == 'install')
		{
			//insert record in #__template_styles
			$query = $db->getQuery(true);
			$query->insert('#__template_styles');
			$query->set('template='.$db->Quote($row->element));
			$query->set('client_id='.$db->Quote($clientId));
			$query->set('home=0');
			$debug = $lang->setDebug(false);
			$query->set('title='.$db->Quote(JText::sprintf('JLIB_INSTALLER_DEFAULT_STYLE', JText::_($this->get('name')))));
			$lang->setDebug($debug);
			$query->set('params='.$db->Quote($row->params));
			$db->setQuery($query);
			$db->query(); // There is a chance this could fail but we don't care...
		}

		return $row->get('extension_id');
	}

	/**
	 * Custom update method for components
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function update()
	{
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @param	int		$id		The extension ID
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$retval	= true;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element)) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the template we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected) {
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_WARNCORETEMPLATE', $row->name));
			return false;
		}

		$name = $row->element;
		$clientId = $row->client_id;

		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'));

			return false;
		}

		// Deny remove default template
		$db = $this->parent->getDbo();
		$query = 'SELECT COUNT(*) FROM #__template_styles'.
				' WHERE home = 1 AND template = '.$db->Quote($name);
		$db->setQuery($query);

		if ($db->loadResult() != 0) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'));

			return false;
		}

		// Get the template root path
		$client = JApplicationHelper::getClientInfo($clientId);

		if (!$client) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'));
			return false;
		}

		$this->parent->setPath('extension_root', $client->path.DS.'templates'.DS.strtolower($name));
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$manifest = $this->parent->getManifest();
		if (!($manifest instanceof JXMLElement)) {
			// kill the extension entry
			$row->delete($row->extension_id);
			unset($row);
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JError::raiseWarning(100, JTEXT::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));

			return false;
		}

		// Remove files
		$this->parent->removeFiles($manifest->media);
		$this->parent->removeFiles($manifest->languages, $clientId);

		// Delete the template directory
		if (JFolder::exists($this->parent->getPath('extension_root'))) {
			$retval = JFolder::delete($this->parent->getPath('extension_root'));
		}
		else {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DIRECTORY'));
			$retval = false;
		}

		//Set menu that assigned to the template back to default template
		$query = 'UPDATE #__menu INNER JOIN #__template_styles'.
				' ON #__template_styles.id = #__menu.template_style_id'.
				' SET #__menu.template_style_id = 0'.
				' WHERE #__template_styles.template = '.$db->Quote(strtolower($name)).
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
	 *
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
			$extension = JTable::getInstance('extension');
			$extension->set('type', 'template');
			$extension->set('client_id', $site_info->id);
			$extension->set('element', $template);
			$extension->set('name', $template);
			$extension->set('state', -1);
			$extension->set('manifest_cache', json_encode($manifest_details));
			$results[] = $extension;
		}

		foreach ($admin_list as $template)
		{
			if ($template == 'system') {
				continue;
				// ignore special system template
			}

			$manifest_details = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR."/templates/$template/templateDetails.xml");
			$extension = JTable::getInstance('extension');
			$extension->set('type', 'template');
			$extension->set('client_id', $admin_info->id);
			$extension->set('element', $template);
			$extension->set('name', $template);
			$extension->set('state', -1);
			$extension->set('manifest_cache', json_encode($manifest_details));
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
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;

		$data = new JObject();

		foreach ($manifest_details as $key => $value)
		{
			$data->set($key, $value);
		}

		$this->parent->extension->params = $this->parent->getParams();

		if ($this->parent->extension->store()) {
			//insert record in #__template_styles
			$db = $this->parent->getDbo();
			$query = $db->getQuery(true);
			$query->insert('#__template_styles');
			$query->set('template='.$db->Quote($this->parent->extension->name));
			$query->set('client_id='.$db->Quote($this->parent->extension->client_id));
			$query->set('home=0');
			$query->set('title='.$db->Quote(JText::sprintf('JLIB_INSTALLER_DEFAULT_STYLE', $this->parent->extension->name)));
			$query->set('params='.$db->Quote($this->parent->extension->params));
			$db->setQuery($query);
			$db->query();

			return $this->parent->extension->get('extension_id');
		}
		else {
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_TPL_DISCOVER_STORE_DETAILS'));

			return false;
		}
	}


	/**
	 * Refreshes the extension table cache
	 * @return  boolean result of operation, true if updated, false on failure
	 * @since	1.6
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path.DS.'templates'. DS.$this->parent->extension->element.DS.'templateDetails.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try {
			return $this->parent->extension->store();
		}
		catch(JException $e) {
			JError::raiseWarning(101, JText::_('JLIB_INSTALLER_ERROR_TPL_REFRESH_MANIFEST_CACHE'));
			return false;
		}
	}
}
