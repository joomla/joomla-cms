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
 * Component installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerComponent extends JAdapterInstance
{
	protected $manifest = null;
	protected $name = null;
	protected $element = null;
	protected $scriptElement = null;
	protected $adminElement	= null;
	protected $installElement = null;
	protected $uninstallElement	= null;
	protected $oldAdminFiles = null;
	protected $oldFiles = null;
	protected $manifest_script = null;
	protected $install_script = null;

	/**
	 * Custom install method for components
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
		$element = strtolower('com_'.JFilterInput::clean($name->data(), 'cmd'));
		$name = $name->data();

		$this->set('element', $element);
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if ($description INSTANCEOF JSimpleXMLElement) {
			$this->parent->set('message', JText::_($description->data()));
		} else {
			$this->parent->set('message', '');
		}

		// Get some important manifest elements
		$this->adminElement		= &$this->manifest->getElementByPath('administration');
		$this->installElement	= &$this->manifest->getElementByPath('install');
		$this->uninstallElement	= &$this->manifest->getElementByPath('uninstall');

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); // copy this as its used as a common base

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (!$this->adminElement INSTANCEOF JSimpleXMLElement)
		{
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('The XML file did not contain an administration element'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If the component site or admin directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */
		if ((file_exists($this->parent->getPath('extension_site')) || file_exists($this->parent->getPath('extension_administrator'))))
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
					JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Another component is already using directory').': "'.$this->parent->getPath('extension_site').'"');
				}
				else { // if the admin exists say that
					JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Another component is already using directory').': "'.$this->parent->getPath('extension_administrator').'"');
				}
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
				include_once $manifestScriptFile;
			}
			// Set the class name
			$classname = $element.'InstallerScript';
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
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'preflight')) $this->parent->manifestClass->preflight('install', $this);
		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

		// If the component directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_site').'"');
				return false;
			}
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// If the component admin directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_administrator')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator')))
			{
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_administrator').'"');
				// Install failed, rollback any changes
				$this->parent->abort();
				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_administrator')));
		}

		// Find files to copy
		foreach ($this->manifest->children() as $child)
		{
			if ($child INSTANCEOF JSimpleXMLElement && $child->name() == 'files')
			{
				if ($this->parent->parseFiles($child) === false) {
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		foreach ($this->adminElement->children() as $child)
		{
			if ($child INSTANCEOF JSimpleXMLElement && $child->name() == 'files')
			{
				if ($this->parent->parseFiles($child, 1) === false)
				{
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->getElementByPath('media'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('languages'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('administration/languages'), 1);

		// Deprecated install, remove after 1.6
		// If there is an install file, lets copy it.
		$installScriptElement = &$this->manifest->getElementByPath('installfile');
		if ($installScriptElement INSTANCEOF JSimpleXMLElement)
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').DS.$installScriptElement->data()) || $this->parent->getOverwrite())
			{
				$path['src']	= $this->parent->getPath('source').DS.$installScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').DS.$installScriptElement->data();
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP install file.'));
					return false;
				}
			}
			$this->set('install_script', $installScriptElement->data());
		}

		// Deprecated uninstall, remove after 1.6
		// If there is an uninstall file, lets copy it.
		$uninstallScriptElement = &$this->manifest->getElementByPath('uninstallfile');
		if ($uninstallScriptElement INSTANCEOF JSimpleXMLElement)
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').DS.$uninstallScriptElement->data()) || $this->parent->getOverwrite())
			{
				$path['src']	= $this->parent->getPath('source').DS.$uninstallScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').DS.$uninstallScriptElement->data();
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP uninstall file.'));
					return false;
				}
			}
		}

		// If there is a manifest script, lets copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source').DS.$this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_administrator').DS.$this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->getOverwrite())
			{
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP manifest file.'));
					return false;
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the install queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('install/sql'));
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		// start legacy support
		if ($this->get('install_script'))
		{
			if (is_file($this->parent->getPath('extension_administrator').DS.$this->get('install_script')) || $this->parent->getOverwrite())
			{
				ob_start();
				ob_implicit_flush(false);
				require_once $this->parent->getPath('extension_administrator').DS.$this->get('install_script');
				if (function_exists('com_install'))
				{
					if (com_install() === false)
					{
						$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
						return false;
					}
				}
				$msg .= ob_get_contents(); // append messages
				ob_end_clean();
			}
		}
		// end legacy support

		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'install')) {
			$this->parent->manifestClass->install($this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Add an entry to the extension table with a whole heap of defaults
		$row = & JTable::getInstance('extension');
		$row->set('name', $this->get('name'));
		$row->set('type', 'component');
		$row->set('element', $this->get('element'));
		$row->set('folder', ''); // There is no folder for components
		$row->set('enabled', 1);
		$row->set('protected', 0);
		$row->set('access', 0);
		$row->set('client_id', 0);
		$row->set('params', $this->parent->getParams());
		$row->set('manifest_cache', $this->parent->generateManifestCache());
		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.$db->stderr(true));
			return false;
		}

		// Time to build the admin menus
		$this->_buildAdminMenus();
		
		// Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('element'),
								'type'=>'component',
								'client_id'=>'',
								'folder'=>''));
		if ($uid) {
			$update->delete($uid);
		}

		// We will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest())
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}

		//TODO: Register the component container just under root in the assets table.

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight')) $this->parent->manifestClass->postflight('install', $this);
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}

		return $row->extension_id;
	}

	/**
	 * Custom update method for components
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function update()
	{
		// Get a database connector object
		$db = &$this->parent->getDbo();

		// set the overwrite setting
		$this->parent->setOverwrite(true);

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
		$element = strtolower('com_'.JFilterInput::clean($name->data(), 'cmd'));
		$name = $name->data();
		$this->set('element', $element);
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', JText::_($description->data()));
		}
		else {
			$this->parent->set('message', '');
		}

		// Get some important manifest elements
		$this->adminElement		= &$this->manifest->getElementByPath('administration');
		$this->installElement	= &$this->manifest->getElementByPath('install');
		$this->uninstallElement	= &$this->manifest->getElementByPath('uninstall');
		$this->scriptElement = &$this->manifest->getElementByPath('scriptfile');

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); // copy this as its used as a common base

		/**
		 * Hunt for the original XML file
		 */
		$oldmanifest = null;
		$tmpInstaller = new JInstaller(); // create a new installer because findManifest sets stuff
		// look in the administrator first
		$tmpInstaller->setPath('source', $this->parent->getPath('extension_administrator'));

		if (!$tmpInstaller->findManifest())
		{
			// then the site
			$tmpInstaller->setPath('source', $this->parent->getPath('extension_site'));
			if ($tmpInstaller->findManifest()) {
				$old_manifest = $tmpInstaller->getManifest();
				$old_manifest = $old_manifest->document;
			}
		}
		else
		{
			$old_manifest = $tmpInstaller->getManifest();
			$old_manifest = $old_manifest->document;
		}

		// should do this above perhaps?
		if ($old_manifest)
		{
			$this->oldAdminFiles = &$old_manifest->getElementByPath('administration/files');
			$this->oldFiles = &$old_manifest->getElementByPath('files');
		}
		else
		{
			$this->oldAdminFiles = null;
			$this->oldFiles = null;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (! is_a($this->adminElement, 'JSimpleXMLElement'))
		{
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Update').': '.JText::_('The XML file did not contain an administration element'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */
		// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
		if (is_a($this->scriptElement, 'JSimpleXMLElement'))
		{
			$manifestScript = $this->scriptElement->data();
			$manifestScriptFile = $this->parent->getPath('source').DS.$manifestScript;
			if (is_file($manifestScriptFile))
			{
				// load the file
				include_once $manifestScriptFile;
			}
			// Set the class name
			$classname = $element.'InstallerScript';
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
			$this->parent->manifestClass->preflight('update', $this);
		}
		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// If the component directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Update').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_site').'"');
				return false;
			}
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// If the component admin directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_administrator')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator')))
			{
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Update').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_administrator').'"');
				// Install failed, rollback any changes
				$this->parent->abort();
				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_administrator')));
		}

		// Find files to copy
		foreach ($this->manifest->children() as $child)
		{
			if (is_a($child, 'JSimpleXMLElement') && $child->name() == 'files')
			{
				if ($this->parent->parseFiles($child, 0, $this->oldFiles) === false)
				{
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		foreach ($this->adminElement->children() as $child)
		{
			if (is_a($child, 'JSimpleXMLElement') && $child->name() == 'files')
			{
				if ($this->parent->parseFiles($child, 1, $this->oldAdminFiles) === false)
				{
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->getElementByPath('media'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('languages'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('administration/languages'), 1);

		// Deprecated install, remove after 1.6
		// If there is an install file, lets copy it.
		$installScriptElement = &$this->manifest->getElementByPath('installfile');
		if (is_a($installScriptElement, 'JSimpleXMLElement'))
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').DS.$installScriptElement->data()) || $this->parent->getOverwrite())
			{
				$path['src']	= $this->parent->getPath('source').DS.$installScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').DS.$installScriptElement->data();
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.JText::_('Could not copy PHP install file.'));
					return false;
				}
			}
			$this->set('install_script', $installScriptElement->data());
		}

		// Deprecated uninstall, remove after 1.6
		// If there is an uninstall file, lets copy it.
		$uninstallScriptElement = &$this->manifest->getElementByPath('uninstallfile');
		if (is_a($uninstallScriptElement, 'JSimpleXMLElement'))
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').DS.$uninstallScriptElement->data()) || $this->parent->getOverwrite())
			{
				$path['src']	= $this->parent->getPath('source').DS.$uninstallScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').DS.$uninstallScriptElement->data();
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.JText::_('Could not copy PHP uninstall file.'));
					return false;
				}
			}
		}

		// If there is a manifest script, lets copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source').DS.$this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_administrator').DS.$this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->getOverwrite())
			{
				if (!$this->parent->copyFiles(array ($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.JText::_('Could not copy PHP manifest file.'));
					return false;
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the install queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('update/sql'));
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
			return false;
		}

		// Time to build the admin menus
		$this->_buildAdminMenus();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an update script, lets include it, execute the custom
		 * update method, and append the return value from the custom update
		 * method to the installation message.
		 */
		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) {
			$this->parent->manifestClass->update($this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('element'),
								'type'=>'component',
								'client_id'=>'',
								'folder'=>''));
		if ($uid) $update->delete($uid);

		// Update an entry to the extension table
		$row = & JTable::getInstance('extension');
		$eid = $row->find(Array('element'=>strtolower($this->get('element')),
						'type'=>'component'));

		if ($eid) {
			$row->load($eid);
		}
		else
		{
			// set the defaults
			$row->folder = ''; // There is no folder for components
			$row->enabled = 1;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = 0;
			$row->params = $this->parent->getParams();
		}
		$row->name = $this->get('name');
		$row->type = 'component';
		$row->element = $this->get('element');
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.$db->stderr(true));
			return false;
		}


		// We will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest())
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Update').': '.JText::_('Could not copy setup file'));
			return false;
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight')) {
			$this->parent->manifestClass->postflight('update', $this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}

		return $row->extension_id;
	}


	/**
	 * Custom uninstall method for components
	 *
	 * @access	public
	 * @param	int		$id	The unique extension id of the component to uninstall
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.0
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$db = &$this->parent->getDbo();
		$row	= null;
		$retval	= true;

		// First order of business will be to load the component object table from the database.
		// This should give us the necessary information to proceed.
		$row = & JTable::getInstance('extension');
		if (!$row->load((int) $id)) {
			JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the component we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected) {
			JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCORECOMPONENT', $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
			return false;
		}

		// Get the admin and site paths for the component
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->element));
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->element));
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); // copy this as its used as a common base

		// Attempt to load the admin language file; might have uninstall strings
		$lang = &JFactory::getLanguage();
		// 1.5 or Core
		$lang->load($row->element);
		// 1.6
		$lang->load($row->element, $this->parent->getPath('extension_administrator'));

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Find and load the XML install file for the component
		$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

		// Get the package manifest objecct
		$manifest = &$this->parent->getManifest();
		if (!$manifest INSTANCEOF JSimpleXML)
		{
			// Make sure we delete the folders if no manifest exists
			JFolder::delete($this->parent->getPath('extension_administrator'));
			JFolder::delete($this->parent->getPath('extension_site'));

			// Remove the menu
			$this->_removeAdminMenus($row);

			// Raise a warning
			JError::raiseWarning(100, JText::_('ERRORREMOVEMANUALLY'));

			// Return
			return false;
		}

		// Get the root node of the manifest document
		$this->manifest = &$manifest->document;

		// Set the extensions name
		$name = &$this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading and Uninstall
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
				include_once $manifestScriptFile;
			}
			// Set the class name
			$classname = $row->element.'InstallerScript';
			if (class_exists($classname))
			{
				// create a new instance
				$this->parent->manifestClass = new $classname($this);
				// and set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
				// Note: if we don't find the class, don't bother to copy the file
			}
		}

		ob_start();
		ob_implicit_flush(false);
		// run uninstall if possible
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'uninstall')) $this->parent->manifestClass->uninstall($this);
		$msg = ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Uninstallation Script Section; Legacy 1.5 Support
		 * ---------------------------------------------------------------------------------------------
		 */

		// Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
		$uninstallfileElement = &$this->manifest->getElementByPath('uninstallfile');
		if ($uninstallfileElement INSTANCEOF JSimpleXMLElement)
		{
			// Element exists, does the file exist?
			if (is_file($this->parent->getPath('extension_administrator').DS.$uninstallfileElement->data()))
			{
				ob_start();
				ob_implicit_flush(false);
				require_once $this->parent->getPath('extension_administrator').DS.$uninstallfileElement->data();
				if (function_exists('com_uninstall'))
				{
					if (com_uninstall() === false)
					{
						JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Custom Uninstall script unsuccessful'));
						$retval = false;
					}
				}
				$msg .= ob_get_contents(); // append this in case there was something else
				ob_end_clean();
			}
		}

		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the uninstall queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('uninstall/sql'));
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
			$retval = false;
		}

		$this->_removeAdminMenus($row);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's remove language files and media in the JROOT/images/ folder that are
		// associated with the component we are uninstalling
		$this->parent->removeFiles($this->manifest->getElementByPath('media'));
		$this->parent->removeFiles($this->manifest->getElementByPath('languages'));
		$this->parent->removeFiles($this->manifest->getElementByPath('administration/languages'), 1);

		 // Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$row->element,
								'type'=>'component',
								'client_id'=>'',
								'folder'=>''));
		if ($uid) $update->delete($uid);

		// Now we need to delete the installation directories.  This is the final step in uninstalling the component.
		if (trim($row->element))
		{
			// Delete the component site directory
			if (is_dir($this->parent->getPath('extension_site')))
			 {
				if (!JFolder::delete($this->parent->getPath('extension_site')))
				{
					JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Unable to remove the component site directory'));
					$retval = false;
				}
			}

			// Delete the component admin directory
			if (is_dir($this->parent->getPath('extension_administrator')))
			{
				if (!JFolder::delete($this->parent->getPath('extension_administrator')))
				{
					JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Unable to remove the component admin directory'));
					$retval = false;
				}
			}

			// Now we will no longer need the extension object, so lets delete it and free up memory
			$row->delete($row->extension_id);
			unset ($row);

			return $retval;
		}
		else
		{
			// No component option defined... cannot delete what we don't know about
			JError::raiseWarning(100, 'Component Uninstall: Option field empty, cannot remove files');
			return false;
		}
	}

	/**
	 * Method to build menu database entries for a component
	 *
	 * @access	private
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	protected function _buildAdminMenus()
	{
		// Initialise variables.
		$db = &$this->parent->getDbo();
		$table = &JTable::getInstance('menu');
		$option = $this->get('element');

		// If a component exists with this option in the table then we don't need to add menus
		$query = new JQuery();
		$query->select('m.id, e.extension_id');
		$query->from('#__menu AS m');
		$query->leftJoin('#__extensions AS e ON m.component_id = e.extension_id');
		$query->where('m.parent_id = 1');
		$query->where('e.element = '.$option);

		$db->setQuery($query);
		
		$componentrow = $db->loadObject();
		
		// Check if menu items exist
		if ($componentrow)
		{

			// Don't do anything if overwrite has not been enabled
			if (! $this->parent->getOverwrite()) {
				return true;
			}

			// Remove existing menu items if overwrite has been enabled
			if ($option)
			{
				$this->_removeAdminMenus($componentrow);// If something goes wrong, theres no way to rollback TODO: Search for better solution
			}
		} else {
			// Lets Find the extension id
			$query = new JQuery();
			$query->select('e.extension_id');
			$query->from('#__extensions AS e');
			$query->where('e.element = '.$option);

			$db->setQuery($query);

			$component_id = $db->loadResult(); // TODO Find Some better way to discover the component_id
		}

		// Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		$menuElement = & $this->adminElement->getElementByPath('menu');
		if ($menuElement INSTANCEOF JSimpleXMLElement)
		{
			$data = array();
			$data['menutype'] = '_adminmenu';
			$data['title'] = $option;
			$data['alias'] = $menuElement->data();
			$data['link'] = 'index.php?option='.$option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $component_id;
			$data['img'] = ($menuElement->attributes('img')) ? $menuElement->attributes('img') : 'class:component';
			$data['home'] = 0;
			
			if(!$table->bind($data) || !$table->check() || !$table->store())
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.$db->stderr(true));
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array ('type' => 'menu'));
		}
		else
		{

			// No menu element was specified, Let's make a generic menu item
			$data = array();
			$data['menutype'] = '_adminmenu';
			$data['title'] = $option;
			$data['alias'] = $option;
			$data['link'] = 'index.php?option='.$option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $component_id;
			$data['img'] = 'class:component';
			$data['home'] = 0;
			
			if(!$table->bind($data) || !$table->check() || !$table->store())
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.$db->stderr(true));
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array ('type' => 'menu'));
		}

		/*
		 * Process SubMenus
		 */

		$submenu = $this->adminElement->getElementByPath('submenu');
		if (!($submenu INSTANCEOF JSimpleXMLElement) || !count($submenu->children())) {
			return true;
		}
		foreach ($submenu->children() as $child)
		{
			if ($child INSTANCEOF JSimpleXMLElement && $child->name() == 'menu')
			{
				$data = array();
				$data['menutype'] = '_adminmenu';
				$data['title'] = $child->attributes('view')? $option.'_'.$child->attributes('view') : $option;
				$data['alias'] = $child->data();
				$data['type'] = 'component';
				$data['published'] = 0;
				$data['parent_id'] = 1;
				$data['component_id'] = $component_id;
				$data['img'] = ($child->attributes('img')) ? $child->attributes('img') : 'class:component';
				$data['home'] = 0;
				
				// Set the sub menu link
				if ($child->attributes("link")) {
					$data['link'] = $child->attributes("link");
				}
				else
				{
					$request = array();
					if ($child->attributes('act')) {
						$request[] = 'act='.$child->attributes('act');
					}
					if ($child->attributes('task')) {
						$request[] = 'task='.$child->attributes('task');
					}
					if ($child->attributes('controller')) {
						$request[] = 'controller='.$child->attributes('controller');
					}
					if ($child->attributes('view')) {
						$request[] = 'view='.$child->attributes('view');
					}
					if ($child->attributes('layout')) {
						$request[] = 'layout='.$child->attributes('layout');
					}
					if ($child->attributes('sub')) {
						$request[] = 'sub='.$child->attributes('sub');
					}
					$qstring = (count($request)) ? '&'.implode('&',$request) : '';
					$data['link'] = "index.php?option=".$option.$qstring;
				}


				if(!$table->bind($data) || !$table->check() || !$table->store())
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.$db->stderr(true));
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$this->parent->pushStep(array ('type' => 'menu'));
			}
		}
	}

	/**
	 * Method to remove admin menu references to a component
	 *
	 * @access	private
	 * @param	object	$component	Component table object
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	protected function _removeAdminMenus(&$row)
	{
		// Initialise Variables
		$db = &$this->parent->getDbo();
		$table = &JTable::getInstance('menu');
		$id = $row->extension_id;
		
		// Get the ids of the menu items
		$query = new JQuery();
		$query->select('id');
		$query->from('#__menu');
		$query->where('`menutype` = "_adminmenu"');
		$query->where('`component_id` = "'.$id.'"');
		
		$ids = $db->loadResultArray();
		
		// Check for error
		if($error = $db->getErrorMsg() || empty($ids)){
			
			JError::raiseWarning('Some_code_here', 'Some_message_here');
			return false;
			
		} else {
			// Iterate the items to delete each one.
			foreach($ids as $menuid){
				
				if (!$table->delete((int) $menuid))
				{
					$this->setError($table->getError());
					return false;
				}
				
			}
			// Rebuild the whole tree
			$table->rebuild();
			
		}
		return true;
	}

	/**
	 * Custom rollback method
	 * - Roll back the component menu item
	 *
	 * @access	public
	 * @param	array	$arg	Installation step to rollback
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	protected function _rollback_menu()
	{
		return true;
	}

	function discover()
	{
		$results = Array();
		$site_components = JFolder::folders(JPATH_SITE.DS.'components');
		$admin_components = JFolder::folders(JPATH_ADMINISTRATOR.DS.'components');
		foreach ($site_components as $component)
		{
			if (file_exists(JPATH_SITE.DS.'components'.DS.$component.DS.str_replace('com_','', $component).'.xml'))
			{
				$extension = &JTable::getInstance('extension');
				$extension->set('type', 'component');
				$extension->set('client_id', 0);
				$extension->set('element', $component);
				$extension->set('name', $component);
				$extension->set('state', -1);
				$results[] = $extension;
			}
		}
		foreach ($admin_components as $component)
		{
			if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.$component.DS.str_replace('com_','', $component).'.xml'))
			{
				$extension = &JTable::getInstance('extension');
				$extension->set('type', 'component');
				$extension->set('client_id', 1);
				$extension->set('element', $component);
				$extension->set('name', $component);
				$extension->set('state', -1);
				$results[] = $extension;
			}
		}
		return $results;
	}

	function discover_install()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = str_replace('com_', '', $this->parent->extension->element);
		$manifestPath = $client->path . DS . 'components'. DS . $this->parent->extension->element . DS . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('source', $client->path . DS . 'components'. DS . $this->parent->extension->element);
		$this->parent->setPath('extension_root', $this->parent->getPath('source'));

		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = serialize($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		$this->parent->extension->params = $this->parent->getParams();
		try {
			$this->parent->extension->store();
		}
		catch(JException $e)
		{
			JError::raiseWarning(101, JText::_('Component').' '.JText::_('Discover Install').': '.JText::_('Failed to store extension details'));
			return false;
		}

		// now we need to run any SQL it has, languages, media or menu stuff

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
		$element = strtolower('com_'.JFilterInput::clean($name->data(), 'cmd'));
		$name = $name->data();
		$this->set('element', $element);
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', JText::_($description->data()));
		}
		else {
			$this->parent->set('message', '');
		}

		// Get some important manifest elements
		$this->adminElement		= &$this->manifest->getElementByPath('administration');
		$this->installElement	= &$this->manifest->getElementByPath('install');
		$this->uninstallElement	= &$this->manifest->getElementByPath('uninstall');

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.$this->get('element')));
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); // copy this as its used as a common base

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (! is_a($this->adminElement, 'JSimpleXMLElement'))
		{
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('The XML file did not contain an administration element'));
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
			if (is_file($manifestScriptFile))
			{
				// load the file
				include_once $manifestScriptFile;
			}
			// Set the class name
			$classname = $element.'InstallerScript';
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
			$this->parent->manifestClass->preflight('discover_install', $this);
		}
		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();

		// Normally we would copy files and create directories, lets skip to the optional files
		// Note: need to dereference things!
		// Parse optional tags
		$this->parent->parseMedia($this->manifest->getElementByPath('media'));
		// We don't do language because 1.6 suggests moving to extension based languages
		//$this->parent->parseLanguages($this->manifest->getElementByPath('languages'));
		//$this->parent->parseLanguages($this->manifest->getElementByPath('administration/languages'), 1);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the install queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('install/sql'));
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
			return false;
		}

		// Time to build the admin menus
		$this->_buildAdminMenus();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		// start legacy support
		if ($this->get('install_script'))
		{
			if (is_file($this->parent->getPath('extension_administrator').DS.$this->get('install_script')))
			{
				ob_start();
				ob_implicit_flush(false);
				require_once $this->parent->getPath('extension_administrator').DS.$this->get('install_script');
				if (function_exists('com_install'))
				{
					if (com_install() === false)
					{
						$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
						return false;
					}
				}
				$msg .= ob_get_contents(); // append messages
				ob_end_clean();
			}
		}
		// end legacy support

		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'discover_install'))
		{
			$this->parent->manifestClass->install($this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		 // Clobber any possible pending updates
		$update = &JTable::getInstance('update');
		$uid = $update->find(Array('element'=>$this->get('element'),
								'type'=>'component',
								'client_id'=>'',
								'folder'=>''));
		if ($uid) {
			$update->delete($uid);
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'postflight'))
		{
			$this->parent->manifestClass->postflight('discover_install', $this);
		}
		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}
		return $this->parent->extension->extension_id;
	}

	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = str_replace('com_', '', $this->parent->extension->element);
		$manifestPath = $client->path . DS . 'components'. DS . $this->parent->extension->element . DS . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JApplicationHelper::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = serialize($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];
		try {
			return $this->parent->extension->store();
		}
		catch(JException $e)
		{
			JError::raiseWarning(101, JText::_('Component').' '.JText::_('Refresh Manifest Cache').': '.JText::_('Failed to store extension details'));
			return false;
		}
	}
}
