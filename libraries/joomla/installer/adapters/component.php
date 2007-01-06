<?php
/**
 * @version		$Id: component.php 6138 2007-01-02 03:44:18Z eddiea $
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

/**
 * Component installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstaller_component extends JObject
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
	 * Custom install method for components
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

		// Set the component name
		$name =& $root->getElementByPath('name');
		$this->set('name', $name->data());

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->get('name'))).DS));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->get('name'))).DS));

		/*
		 * If the component directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */
		if (file_exists($this->parent->getPath('extension_site')) && !$this->parent->getOverwrite()) {
			JError::raiseWarning(1, 'Component Install: '.JText::_('Another component is already using directory').': "'.$this->parent->getPath('extension_site').'"');
			return false;
		}

		/*
		 * If the component directory does not exist, lets create it
		 */
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_site'))) {
				JError::raiseWarning(1, 'Component Install: '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_site').'"');
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

		/*
		 * If the component admin directory does not exist, lets create it as well
		 */
		$created = false;
		if (!file_exists($this->parent->getPath('extension_administrator'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator'))) {
				JError::raiseWarning(1, 'Component Install: '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_administrator').'"');
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
		if ($this->parent->parseFiles($root->getElementByPath('files')) === false) {
			// Install failed, rollback any changes
			$this->parent->abort();
			return false;
		}
		if ($this->parent->parseFiles($root->getElementByPath('administration/files'), 1) === false) {
			// Install failed, rollback any changes
			$this->parent->abort();
			return false;
		}

		// Parse optional files tags
		$this->parent->parseFiles($root->getElementByPath('images'));
		$this->parent->parseFiles($root->getElementByPath('administration/images'), 1);
		$this->parent->parseFiles($root->getElementByPath('media'));
		$this->parent->parseFiles($root->getElementByPath('administration/media'), 1);
		$this->parent->parseFiles($root->getElementByPath('languages'));
		$this->parent->parseFiles($root->getElementByPath('administration/languages'), 1);

		/*
		 * Let's run the install queries for the component
		 *	If backward compatibility is required - run queries in xml file
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		$result = $this->parent->parseQueries($root->getElementByPath('install/queries'));
		if ($result === false) {
			// Install failed, rollback changes
			$this->parent->abort('Component Install: '.JText::_('SQL Error')." ".$db->stderr(true));
			return false;
		} elseif ($result === 0) {
			// no backward compatibility queries found - try for Joomla 1.5 type queries
			// second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($root->getElementByPath('install/sql'));
			if ($utfresult === false) {
				// Install failed, rollback changes
				$this->parent->abort('Component Install: '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
				return false;
			}
		}

		// If there is an install file, lets copy it.
		$installScriptElement = & $root->getElementByPath('installfile');
		if (is_a($installScriptElement, 'JSimpleXMLElement')) {
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').$installScriptElement->data()))
			{
				$path['src']	= $this->parent->getPath('source').$installScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').$installScriptElement->data();
				if (!$this->parent->copyFiles(array ($path))) {
					// Install failed, rollback changes
					$this->parent->abort('Component Install: '.JText::_('Could not copy PHP install file.'));
					return false;
				}
			}
			$this->set('installscript', $installScriptElement->data());
		}

		// If there is an uninstall file, lets copy it.
		$uninstallScriptElement = & $root->getElementByPath('uninstallfile');
		if (is_a($uninstallScriptElement, 'JSimpleXMLElement')) {
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->parent->getPath('extension_administrator').$uninstallScriptElement->data()))
			{
				$path['src']	= $this->parent->getPath('source').$uninstallScriptElement->data();
				$path['dest']	= $this->parent->getPath('extension_administrator').$uninstallScriptElement->data();
				if (!$this->parent->copyFiles(array ($path))) {
					// Install failed, rollback changes
					$this->parent->abort('Component Install: '.JText::_('Could not copy PHP uninstall file.'));
					return false;
				}
			}
		}

		// Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		$adminMenuElement = & $root->getElementByPath('administration/menu');
		if (is_a($adminMenuElement, 'JSimpleXMLElement')) {
			// Initialize some variables
			$comName = strtolower("com_".str_replace(" ", "", $this->get('name')));
			$comAdminMenuName = $adminMenuElement->data();
			$comImg = "js/ThemeOffice/component.png";

			// Handle custom menu image
			if ($adminMenuElement->attributes('img')) {
				$comImg = $adminMenuElement->attributes('img');
			}

			// Do we have submenus?
			if (count($adminMenuElement->children())) {
				// Lets create the component root menu item
				$comAdminMenuId = $this->_createParentMenu($comAdminMenuName, $comName, $comImg);
				if ($comAdminMenuId === false) {
					// Install failed, rollback changes
					$this->parent->abort();
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$this->parent->pushStep(array ('type' => 'menu', 'id' => $comAdminMenuId));

				// Get the submenus array
				$comAdminSubMenus = $adminMenuElement->children();

				// SubMenu Ordering value
				$subMenuOrdering = 0;

				/*
				 * Lets build the submenus
				 */
				foreach ($comAdminSubMenus as $adminSubMenu)
				{
					if ($adminSubMenu->name() != 'submenu') {
						continue;
					}

					$com = JTable::getInstance('component');
					$com->name = $adminSubMenu->data();
					$com->link = '';
					$com->menuid = 0;
					$com->parent = $comAdminMenuId;
					$com->iscore = 0;
					$com->admin_menu_alt = $adminSubMenu->data();
					$com->option = $comName;
					$com->ordering = $subMenuOrdering ++;

					// Set the sub menu link
					if ($adminSubMenu->attributes("act")) {
						$com->admin_menu_link = "option=$comName&act=".$adminSubMenu->attributes("act");
					} elseif ($adminSubMenu->attributes("task")) {
							$com->admin_menu_link = "option=$comName&task=".$adminSubMenu->attributes("task");
					} else {
						if ($adminSubMenu->attributes("link")) {
							$com->admin_menu_link = $adminSubMenu->attributes("link");
						} else {
							$com->admin_menu_link = "option=$comName";
						}
					}

					// Set the submenu image
					if ($adminSubMenu->attributes("img")) {
						$com->admin_menu_img = $adminSubMenu->attributes("img");
					} else {
						$com->admin_menu_img = "js/ThemeOffice/component.png";
					}

					// Store the submenu
					if (!$com->store()) {
						// Install failed, rollback changes
						$this->parent->abort('Component Install: '.JText::_('SQL Error')." ".$db->stderr(true));
						return false;
					}

					/*
					 * Since we have created a menu item, we add it to the installation step stack
					 * so that if we have to rollback the changes we can undo it.
					 */
					$this->parent->pushStep(array ('type' => 'menu', 'id' => $com->_db->insertid()));
				}
			} else {
				// No submenus, just create the component root menu item
				$menuid = $this->_createParentMenu($comAdminMenuName, $comName, $comImg);
				if ($menuid === false) {
					// Install failed, rollback changes
					$this->parent->abort();
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$this->parent->pushStep(array ('type' => 'menu', 'id' => $menuid));
			}
		} else {
			// No menu entry, lets just enter a component entry to the table.
			$db_name = $this->get('name');
			$db_link = "";
			$db_menuid = 0;
			$db_parent = 0;
			$db_admin_menu_link = "";
			$db_admin_menu_alt = $this->get('name');
			$db_option = strtolower("com_".str_replace(" ", "", $this->get('name')));
			$db_ordering = 0;
			$db_admin_menu_img = "";
			$db_iscore = 0;
			$db_params = $this->parent->getParams();
			$db_enabled = 1;

			// Get database connector object
			$query = "INSERT INTO #__components" .
					"\n VALUES( '', '$db_name', '$db_link', $db_menuid, $db_parent, '$db_admin_menu_link', '$db_admin_menu_alt', '$db_option', $db_ordering, '$db_admin_menu_img', $db_iscore, '$db_params', '$db_enabled' )";
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::install: '.$db->stderr(true));
				$this->parent->abort();
				return false;
			}

			$menuid = $this->_db->insertid();
			if ($menuid === false) {
				// Install failed, rollback changes
				$this->parent->abort();
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array ('type' => 'menu', 'id' => $menuid));
		}

		// Get the component description
		$description = & $root->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $this->get('name').'<p>'.$description->data().'</p>');
		} else {
			$this->parent->set('message', $this->get('name'));
		}

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		if ($this->get('installscript')) {
			if (is_file($this->parent->getPath('extension_administrator').$this->get('installscript'))) {
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->parent->getPath('extension_administrator').$this->get('installscript'));
				$ret = com_install();
				$ret .= ob_get_contents();
				ob_end_clean();
				if ($ret != '') {
					$this->parent ->set('extension.message', $ret);
				}
			}
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest()) {
			// Install failed, rollback changes
			$this->parent->abort('Component Install: '.JText::_('Could not copy setup file'));
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method for components
	 *
	 * @access	public
	 * @param	int		$cid	The id of the component to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.0
	 */
	function uninstall($id, $clientId)
	{
		// Initialize variables
		$db =& $this->parent->getDBO();
		$row	= null;
		$retval	= true;

		// First order of business will be to load the component object table from the database.
		// This should give us the necessary information to proceed.
		$row = & JTable::getInstance('component');
		$row->load($id);

		// Is the component we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->iscore) {
			JError::raiseWarning(100, 'Component Uninstall: '.JText::sprintf('WARNCORECOMPONENT', $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
			return false;
		}

		// Get the admin and site paths for the component
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option));
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->option));

		// Find and load the XML install file for the component
		$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

		// Next, lets delete the submenus for the component.
		$sql = "DELETE " .
				"\nFROM #__components " .
				"\nWHERE parent = ".(int)$row->id;
		$db->setQuery($sql);
		if (!$db->query()) {
			JError::raiseWarning(100, 'Component Uninstall: '.$db->stderr(true));
			$retval = false;
		}

		// Next, we will delete the component object
		if (!$row->delete($row->id)) {
			JError::raiseWarning(100, 'Component Uninstall: '.JText::_('Unable to delete the component from the database'));
			$retval = false;
		}

		// Get the package manifest objecct
		$manifest =& $this->parent->getManifest();
		if (!is_a($manifest, 'JSimpleXML')) {
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_administrator'));
			JFolder::delete($this->parent->getPath('extension_site'));
			JError::raiseWarning(100, 'Component Uninstall: Package manifest file invalid or not found');
			return false;
		}

		// Get the root node of the manifest document
		$root =& $manifest->document;

		// Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
		$uninstallfileElement =& $root->getElementByPath('uninstallfile');
		if (is_a($uninstallfileElement, 'JSimpleXMLElement')) {
			// Element exists, does the file exist?
			if (file_exists($this->parent->getPath('extension_administrator').$uninstallfileElement->data())) {
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->parent->getPath('extension_administrator').$uninstallfileElement->data());
				$ret = com_uninstall();
				$ret .= ob_get_contents();
				ob_end_clean();
				if ($ret != '') {
					$this->parent->set('extension.message', $ret);
				}
			}
		}

		/*
		 * Let's run the uninstall queries for the component
		 *	If backward compatibility is required - run queries in xml file
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		$result = $this->parent->parseQueries($root->getElementByPath('uninstall/queries'));
		if ($result === false) {
			// Install failed, rollback changes
			JError::raiseWarning(100, 'Component Uninstall: '.JText::_('SQL Error')." ".$db->stderr(true));
			$retval = false;
		} elseif ($result === 0) {
			// no backward compatibility queries found - try for Joomla 1.5 type queries
			// second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($root->getElementByPath('uninstall/sql'));
			if ($utfresult === false) {
				// Install failed, rollback changes
				JError::raiseWarning(100, 'Component Uninstall: '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
				$retval = false;
			}
		}

		// Let's remove language files and media in the JROOT/images/ folder that are
		// associated with the component we are uninstalling
		$this->parent->removeFiles($root->getElementByPath('media'));
		$this->parent->removeFiles($root->getElementByPath('media'), 1);
		$this->parent->removeFiles($root->getElementByPath('languages'));
		$this->parent->removeFiles($root->getElementByPath('administration/languages'), 1);

		// Now we need to delete the installation directories.  This is the final step in uninstalling the component.
		if (trim($row->option)) {
			// Delete the component site directory
			if (is_dir($this->parent->getPath('extension_site'))) {
				if (!JFolder::delete($this->parent->getPath('extension_site'))) {
					JError::raiseWarning(100, 'Component Uninstall: '.JText::_('Unable to remove the component site directory'));
					$retval = false;
				}
			}

			// Delete the component admin directory
			if (is_dir($this->parent->getPath('extension_administrator'))) {
				if (!JFolder::delete($this->parent->getPath('extension_administrator'))) {
					JError::raiseWarning(100, 'Component Uninstall: '.JText::_('Unable to remove the component admin directory'));
					$retval = false;
				}
			}
			return $retval;
		} else {
			// No component option defined... cannot delete what we don't know about
			JError::raiseWarning(100, 'Component Uninstall: Option field empty, cannot remove files');
			return false;
		}
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the component menu item
	 *
	 * @access	public
	 * @param	array	$arg	Installation step to rollback
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _rollback_menu($arg)
	{
		// Get database connector object
		$db =& $this->parent->getDBO;

		// Remove the entry from the #__components table
		$query = "DELETE " .
				"\nFROM `#__components` " .
				"\nWHERE id=".(int)$arg['id'];
		$db->setQuery($query);
		return ($db->query() !== false);
	}

	/**
	 * Method to create a menu entry for a component
	 *
	 * @access	private
	 * @param	string	$_menuname	Menu name
	 * @param	string	$_comname	Component name
	 * @param	string	$_image		Image file
	 * @return	int		Id of the created menu entry
	 * @since	1.0
	 */
	function _createParentMenu($_menuname, $_comname, $_image = "js/ThemeOffice/component.png")
	{
		// Get database connector object
		$db =& $this->parent->getDBO();

		$db_name = $_menuname;
		$db_link = "option=$_comname";
		$db_menuid = 0;
		$db_parent = 0;
		$db_admin_menu_link = "option=$_comname";
		$db_admin_menu_alt = $_menuname;
		$db_option = $_comname;
		$db_ordering = 0;
		$db_admin_menu_img = $_image;
		$db_iscore = 0;
		$db_params = $this->parent->getParams();
		$db_enabled = 1;

		$query = "INSERT INTO #__components" .
				"\n VALUES( '', '$db_name', '$db_link', $db_menuid, $db_parent, '$db_admin_menu_link', '$db_admin_menu_alt', '$db_option', $db_ordering, '$db_admin_menu_img', $db_iscore, '$db_params', '$db_enabled' )";
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseWarning(100, 'Component Install: '.$db->stderr(true));
			return false;
		}
		$menuid = $db->insertid();
		return $menuid;
	}
}
?>