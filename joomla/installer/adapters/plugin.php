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
 * Plugin installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerPlugin extends JAdapterInstance
{
	/** @var string install function routing */
	var $route = 'Install';

	protected $manifest = null;
	protected $manifest_script = null;
	protected $name = null;
	protected $scriptElement = null;

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function install()
	{
		// Get a database connector object
		$db = &$this->parent->getDbo();

		// Get the extension manifest object
		$manifest = &$this->parent->getManifest();
		$this->manifest = &$manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name = &$this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'string');
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if ($description INSTANCEOF JSimpleXMLElement) {
			$this->parent->set('message', $description->data());
		}
		else {
			$this->parent->set('message', '');
		}

		/*
		 * Backward Compatability
		 * @todo Deprecate in future version
		 */
		$type = $this->manifest->attributes('type');

		// Set the installation path
		$plugin_files = &$this->manifest->getElementByPath('files');
		if ($plugin_files INSTANCEOF JSimpleXMLElement && count($plugin_files->children()))
		{
			$files = &$plugin_files->children();
			foreach ($files as $file)
			{
				if ($file->attributes($type))
				{
					$element = $file->attributes($type);
					break;
				}
			}
		}
		$group = $this->manifest->attributes('group');
		if (!empty ($element) && !empty($group)) {
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$group.DS.$element);
		}
		else
		{
			$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('No plugin file specified'));
			return false;
		}


		/*
		 * Check if we should enable overwrite settings
		 */
		// Check to see if a plugin by the same name is already installed
		$query = 'SELECT `extension_id`' .
				' FROM `#__extensions`' .
				' WHERE folder = '.$db->Quote($group) .
				' AND element = '.$db->Quote($element);
		$db->setQuery($query);
		try {
			$db->Query();
		}
		catch(JException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.$db->stderr(true));
			return false;
		}
		$id = $db->loadResult();

		// if its on the fs...
		if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->getOverwrite() || $this->parent->getUpgrade()))
		{
			$updateElement = $this->manifest->getElementByPath('update');
			// upgrade manually set
			// update function available
			// update tag detected
			if ($this->parent->getUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JSimpleXMLElement'))
			{
				// force these one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);
				if ($id) { // if there is a matching extension mark this as an update; semantics really
					$this->route = 'Update';
				}
			}
			else if (!$this->parent->getOverwrite())
			{
				// overwrite is set
				// we didn't have overwrite set, find an udpate function or find an update tag so lets call it safe
				$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('Another extension is already using directory').': "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */
		// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
		$this->scriptElement = &$this->manifest->getElementByPath('scriptfile');
		if (is_a($this->scriptElement, 'JSimpleXMLElement'))
		{
			$manifestScript = $this->scriptElement->data();
			$manifestScriptFile = $this->parent->getPath('source').DS.$manifestScript;
			if (is_file($manifestScriptFile))
			{
				// load the file
				include_once($manifestScriptFile);
			}
			// Set the class name
			$classname = 'plg'.$group.$element.'InstallerScript';
			if (class_exists($classname))
			{
				// create a new instance
				$this->parent->manifestClass = new $classname($this);
				// and set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
				// Note: if we don't find the class, don't bother to copy the file
			}
		}

		// run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'preflight')) {
			$this->parent->manifestClass->preflight($this->route, $this);
		}
		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

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
				$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
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
		if ($this->parent->parseFiles($plugin_files, -1) === false)
		{
			// Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		// Parse optional tags -- media and language files for plugins go in admin app
		$this->parent->parseMedia($this->manifest->getElementByPath('media'), 1);
		$this->parent->parseLanguages($this->manifest->getElementByPath('languages'), 1);

		// If there is a manifest script, lets copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source').DS.$this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_root').DS.$this->get('manifest_script');

			if (!file_exists($path['dest']))
			{
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('Could not copy PHP manifest file.'));
					return false;
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Was there a plugin already installed with the same name?
		if ($id)
		{
			if (!$this->parent->getOverwrite())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('Plugin').' "'. $this->get('name') .'" '.JText::_('already exists!'));
				return false;
			}

		}
		else
		{
			// Store in the extensions table (1.6)
			$row = & JTable::getInstance('extension');
			$row->name = $this->get('name');
			$row->type = 'plugin';
			$row->ordering = 0;
			$row->element = $element;
			$row->folder = $group;
			$row->enabled = 0;
			$row->protected = 0;
			$row->access = 0;
			$row->client_id = 0;
			$row->params = $this->parent->getParams();
			$row->custom_data = ''; // custom data
			$row->system_data = ''; // system data
			$row->manifest_cache = $this->parent->generateManifestCache();

			// Editor plugins are published by default
			if ($group == 'editors') {
				$row->enabled = 1;
			}

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.$db->stderr(true));
				return false;
			}

			// Since we have created a plugin item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(array ('type' => 'extension', 'id' => $row->extension_id));
			$id = $row->extension_id;
		}

		/*
		 * Let's run the queries for the module
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath(strtolower($this->route).'/sql'));
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Module').' '.JText::_($this->route).': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
			return false;
		}

		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,$this->route)) {
			$this->parent->manifestClass->{$this->route}($this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Plugin').' '.JText::_($this->route).': '.JText::_('Could not copy setup file'));
			return false;
		}
		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight')) {
			$this->parent->manifestClass->postflight($this->route, $this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}
		return $id;
	}

	/**
	 * Custom update method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function update()
	{
		// set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);
		// set the route for the install
		$this->route = 'Update';
		// go to install which handles updates properly
		return $this->install();
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$cid	The id of the plugin to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		// Initialize variables
		$row	= null;
		$retval = true;
		$db		= &$this->parent->getDbo();

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = & JTable::getInstance('extension');
		if (!$row->load((int) $id))
		{
			JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the plugin we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCOREPLUGIN', $row->name)."<br />".JText::_('WARNCOREPLUGIN2'));
			return false;
		}

		// Get the plugin folder so we can properly build the plugin path
		if (trim($row->folder) == '')
		{
			JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Folder field empty, cannot remove files'));
			return false;
		}

		// Set the plugin root path
		if (is_dir(JPATH_ROOT.DS.'plugins'.DS.$row->folder.DS.$row->element)) {
			// Use 1.6 plugins
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$row->folder.DS.$row->element);
		}
		else {
			// Use Legacy 1.5 plugins
			$this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$row->folder);
		}

		// Because plugins don't have their own folders we cannot use the standard method of finding an installation manifest
		// Since 1.6 they do, however until we move to 1.7 and remove 1.6 legacy we still need to use this method
		// when we get there it'll be something like "$manifest = &$this->parent->getManifest();"
		$manifestFile = $this->parent->getPath('extension_root').DS.$row->element.'.xml';
		if (file_exists($manifestFile))
		{
			$xml = &JFactory::getXMLParser('Simple');

			// If we cannot load the xml file return null
			if (!$xml->loadFile($manifestFile))
			{
				JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Could not load manifest file'));
				return false;
			}

			/*
			 * Check for a valid XML root tag.
			 * @todo: Remove backwards compatability in a future version
			 * Should be 'extension', but for backward compatability we will accept 'install'.
			 */
			$root = &$xml->document;
			$this->manifest = &$xml->document;
			if ($root->name() != 'install' && $root->name() != 'extension')
			{
				JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Invalid manifest file'));
				return false;
			}

			/**
			 * ---------------------------------------------------------------------------------------------
			 * Installer Trigger Loading
			 * ---------------------------------------------------------------------------------------------
			 */
			// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
			$this->scriptElement = &$this->manifest->getElementByPath('scriptfile');
			if (is_a($this->scriptElement, 'JSimpleXMLElement'))
			{
				$manifestScript = $this->scriptElement->data();
				$manifestScriptFile = $this->parent->getPath('source').DS.$manifestScript;
				if (is_file($manifestScriptFile)) {
					// load the file
					include_once($manifestScriptFile);
				}
				// Set the class name
				$classname = 'plg'.$row->folder.$row->element.'InstallerScript';
				if (class_exists($classname))
				{
					// create a new instance
					$this->parent->manifestClass = new $classname($this);
					// and set this so we can copy it later
					$this->set('manifest_script', $manifestScript);
					// Note: if we don't find the class, don't bother to copy the file
				}
			}

			// run preflight if possible (since we know we're not an update)
			ob_start();
			ob_implicit_flush(false);
			if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'preflight')) {
				$this->parent->manifestClass->preflight($this->route, $this);
			}
			$msg = ob_get_contents(); // create msg object; first use here
			ob_end_clean();

			/*
			 * Let's run the queries for the module
			 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
			 *	file for utf-8 support or non-utf-8 support
			 */
			// try for Joomla 1.5 type queries
			// second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath(strtolower($this->route).'/sql'));
			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
				return false;
			}

			// Start Joomla! 1.6
			ob_start();
			ob_implicit_flush(false);
			if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'uninstall')) {
				$this->parent->manifestClass->uninstall($this);
			}
			$msg = ob_get_contents(); // append messages
			ob_end_clean();


			// Remove the plugin files
			$this->parent->removeFiles($root->getElementByPath('images'), -1);
			$this->parent->removeFiles($root->getElementByPath('files'), -1);
			JFile::delete($manifestFile);

			// Remove all media and languages as well
			$this->parent->removeFiles($root->getElementByPath('media'));
			$this->parent->removeFiles($root->getElementByPath('languages'), -1);
		}
		else
		{
			JError::raiseWarning(100, 'Plugin Uninstall: Manifest File invalid or not found');
			return false;
		}

		// Now we will no longer need the plugin object, so lets delete it
		$row->delete($row->extension_id);
		unset ($row);

		// If the folder is empty, let's delete it
		$files = JFolder::files($this->parent->getPath('extension_root'));
		if (!count($files)) {
			JFolder::delete($this->parent->getPath('extension_root'));
		}

		if ($msg) {
			$this->parent->set('extension_message',$msg);
		}

		return $retval;
	}

	/**
	 * Custom discover method
	 *
	 * @access public
	 * @return array(JExtension) list of extensions available
	 * @since 1.6
	 */
	function discover()
	{
		$results = Array();
		$folder_list = JFolder::folders(JPATH_SITE.DS.'plugins');

		foreach ($folder_list as $folder)
		{
			$file_list = JFolder::files(JPATH_SITE.DS.'plugins'.DS.$folder,'\.xml$');
			foreach ($file_list as $file)
			{
				$file = JFile::stripExt($file);
				if ($file == 'example') continue; // ignore example plugins
				$extension = &JTable::getInstance('extension');
				$extension->set('type', 'plugin');
				$extension->set('client_id', 0);
				$extension->set('element', $file);
				$extension->set('folder', $folder);
				$extension->set('name', $file);
				$extension->set('state', -1);
				$results[] = $extension;
			}
			$folder_list = JFolder::folders(JPATH_SITE.DS.'plugins'.DS.$folder);
			foreach ($folder_list as $plugin_folder)
			{
				$file_list = JFolder::files(JPATH_SITE.DS.'plugins'.DS.$folder.DS.$plugin_folder,'\.xml$');
				foreach ($file_list as $file)
				{
					$file = JFile::stripExt($file);
					if ($file == 'example') continue; // ignore example plugins
					$extension = &JTable::getInstance('extension');
					$extension->set('type', 'plugin');
					$extension->set('client_id', 0);
					$extension->set('element', $file);
					$extension->set('folder', $folder);
					$extension->set('name', $file);
					$extension->set('state', -1);
					$results[] = $extension;
				}
			}
		}
		return $results;
	}

	/**
	 * Custom discover_install method
	 *
	 * @access public
	 * @param int $id The id of the extension to install (from #__discoveredextensions)
	 * @return void
	 * @since 1.6
	 */
	function discover_install()
	{
		// Plugins use the extensions table as their primary store
		// Similar to modules and templates, rather easy
		// If its not in the extensions table we just add it
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		if (is_dir($client->path . DS . 'plugins'. DS . $this->parent->extension->folder . DS . $this->parent->extension->element)) {
			$manifestPath = $client->path . DS . 'plugins'. DS . $this->parent->extension->folder . DS . $this->parent->extension->element . DS . $this->parent->extension->element . '.xml';
		}
		else {
			$manifestPath = $client->path . DS . 'plugins'. DS . $this->parent->extension->folder . DS . $this->parent->extension->element . '.xml';
		}
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
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
			JError::raiseWarning(101, JText::_('Plugin').' '.JText::_('Discover Install').': '.JText::_('Failed to store extension details'));
			return false;
		}
	}

	function refreshManifestCache()
	{
		// Plugins use the extensions table as their primary store
		// Similar to modules and templates, rather easy
		// If its not in the extensions table we just add it
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . DS . 'plugins'. DS . $this->parent->extension->folder . DS . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = serialize($manifest_details);

		$this->parent->extension->name = $manifest_details['name'];
		if ($this->parent->extension->store()) {
			return true;
		}
		else
		{
			JError::raiseWarning(101, JText::_('Plugin').' '.JText::_('Refresh Manifest Cache').': '.JText::_('Failed to store extension details'));
			return false;
		}
	}
}
