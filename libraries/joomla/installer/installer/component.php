<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
class JInstallerComponent extends JInstaller
{

	/**
	 * Custom install method for components
	 *
	 * @access	public
	 * @param	string	$p_fromdir	Directory from which to install the
	 * component
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function install($p_fromdir)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'component'))
		{
			return false;
		}

		// Get the root node of the XML document
		$root = & $this->_xmldoc->documentElement;

		/*
		 * Set the component name
		 */
		$e = & $root->getElementsByPath('name', 1);
		$this->_extensionName = $e->getText();

		/*
		 * Set the installation target paths
		 */
		$this->_extensionDir = JPath::clean(JPATH_SITE.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->_extensionName)).DS);
		$this->_extensionAdminDir = JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->_extensionName)).DS);

		/*
		 * If the component directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */
		if (file_exists($this->_extensionDir))
		{
			JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Another component is already using directory').': "'.$this->_extensionDir.'"');
			return false;
		}

		/*
		 * If the component directory does not exists, lets create it
		 */
		if (!file_exists($this->_extensionDir))
		{
			if (!$created = JFolder::create($this->_extensionDir))
			{
				JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Failed to create directory').': "'.$this->_extensionDir.'"');
				return false;
			}
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		if ($created)
		{
			$this->_stepStack[] = array ('type' => 'folder', 'path' => $this->_extensionDir);
		}

		/*
		 * If the component admin directory does not exist, lets create it as well
		 */
		if (!file_exists($this->_extensionAdminDir))
		{
			if (!$created = JFolder::create($this->_extensionAdminDir))
			{
				JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Failed to create directory').': "'.$this->_extensionAdminDir.'"');

				// Install failed, rollback any changes
				$this->_rollback();
				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, lets add it to the installation step stack
		 */
		if ($created)
		{
			$this->_stepStack[] = array ('type' => 'folder', 'path' => $this->_extensionAdminDir);
		}

		// Find files to copy
		if ($this->_parseFiles('files') === false)
		{

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}
		if ($this->_parseFiles('administration/files', '', '', 1) === false)
		{

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Parse optional files tags
		 */
		$this->_parseFiles('images');
		$this->_parseFiles('administration/images', '', '', 1);
		$this->_parseFiles('media');
		$this->_parseFiles('languages');
		$this->_parseFiles('administration/languages');

		/*
		 * Let's run the install queries for the component
		 *    If backward compatibility is required - run queries in xml file
		 *    If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *    file for utf-8 support or non-utf-8 support
		 */

		// start with backward compatibility <queries> tag
		$result = $this->_parseBackwardQueries('install/queries');

		if ($result === false)
		{
			JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('SQL Error')." ".$db->stderr(true));

			// Install failed, rollback changes
			$this->_rollback();
			return false;
		} else
			if ($result === 0)
			{
				// no backward compatibility queries found - try for Joomla 1.5 type queries
				// second argument is the utf compatible version attribute
				$utfresult = $this->_parseQueries("install/sql/file", ($db->hasUTF() ? '4.1.2' : '3.2.0'));
				if ($utfresult === false)
				{
					JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));

					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}

		/*
		 * If there is an install file, lets copy it.
		 */
		$installScriptElement = & $root->getElementsByPath('installfile', 1);
		if (!is_null($installScriptElement))
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->_extensionAdminDir.$installScriptElement->getText()))
			{
				if (!$this->_copyFiles($this->_installDir, $this->_extensionAdminDir, array ($installScriptElement->getText())))
				{
					JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Could not copy PHP install file.'));

					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}
			$this->_hasInstallScript = true;
			$this->_installScript = $installScriptElement->getText();
		}

		/*
		 * If there is an uninstall file, lets copy it.
		 */
		$uninstallScriptElement = & $root->getElementsByPath('uninstallfile', 1);
		if (!is_null($uninstallScriptElement))
		{
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->_extensionAdminDir.$uninstallScriptElement->getText()))
			{
				if (!$this->_copyFiles($this->_installDir, $this->_extensionAdminDir, array ($uninstallScriptElement->getText())))
				{
					JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Could not copy PHP uninstall file.'));

					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}
		}

		/*
		 * Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		 */
		$adminMenuElement = & $root->getElementsByPath('administration/menu', 1);
		if (!is_null($adminMenuElement))
		{

			// Initialize some variables
			$adminSubmenuElement = & $root->getElementsByPath('administration/submenu', 1);
			$comName = strtolower("com_".str_replace(" ", "", $this->_extensionName));
			$comAdminMenuName = $adminMenuElement->getText();
			$comImg = "js/ThemeOffice/component.png";

			/*
			 * Handle custom menu image
			 */
			if ($adminMenuElement->hasAttribute('img'))
			{
				$comImg = $adminMenuElement->getAttribute('img');
			}

			/*
			 * Do we have submenus?
			 */
			if (!is_null($adminSubmenuElement))
			{

				// Lets create the component root menu item
				$comAdminMenuId = $this->_createParentMenu($comAdminMenuName, $comName, $comImg);
				if ($comAdminMenuId === false)
				{

					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$this->_stepStack[] = array ('type' => 'menu', 'id' => $comAdminMenuId);

				// Get the submenus array
				$comAdminSubMenus = $adminSubmenuElement->childNodes;

				// SubMenu Ordering value
				$subMenuOrdering = 0;

				/*
				 * Lets build the submenus
				 */
				foreach ($comAdminSubMenus as $adminSubMenu)
				{
					$com = JTable::getInstance('component', $db);
					$com->name = $adminSubMenu->getText();
					$com->link = '';
					$com->menuid = 0;
					$com->parent = $comAdminMenuId;
					$com->iscore = 0;
					$com->admin_menu_alt = $adminSubMenu->getText();
					$com->option = $comName;
					$com->ordering = $subMenuOrdering ++;

					/*
					 * Set the sub menu link
					 */
					if ($adminSubMenu->getAttribute("act"))
					{
						$com->admin_menu_link = "option=$comName&act=".$adminSubMenu->getAttribute("act");
					} else
						if ($adminSubMenu->getAttribute("task"))
						{
							$com->admin_menu_link = "option=$comName&task=".$adminSubMenu->getAttribute("task");
						} else
							if ($adminSubMenu->getAttribute("link"))
							{
								$com->admin_menu_link = $adminSubMenu->getAttribute("link");
							} else
							{
								$com->admin_menu_link = "option=$comName";
							}

					/*
					 * Set the sub menu image
					 */
					if ($adminSubMenu->getAttribute("img"))
					{
						$com->admin_menu_img = $adminSubMenu->getAttribute("img");
					} else
					{
						$com->admin_menu_img = "js/ThemeOffice/component.png";
					}

					/*
					 * Store the sub menu
					 */
					if (!$com->store())
					{
						JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('SQL Error')." ".$db->stderr(true));

						// Install failed, rollback changes
						$this->_rollback();
						return false;
					}

					/*
					 * Since we have created a menu item, we add it to the installation step stack
					 * so that if we have to rollback the changes we can undo it.
					 */
					$this->_stepStack[] = array ('type' => 'menu', 'id' => $com->_db->insertid());
				}
			} else
			{

				// No submenus, just create the component root menu item
				$menuid = $this->_createParentMenu($comAdminMenuName, $comName, $comImg);
				if ($menuid === false)
				{

					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$this->_stepStack[] = array ('type' => 'menu', 'id' => $menuid);
			}
		}

		/*
		 * Get the component description
		 */
		$e = & $root->getElementsByPath('description', 1);
		if (!is_null($e))
		{
			$this->description = $this->_extensionName.'<p>'.$e->getText().'</p>';
		} else
		{
			$this->description = $this->_extensionName;
		}

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		if ($this->_hasInstallScript)
		{
			if (is_file($this->_extensionAdminDir.DS.$this->_installScript))
			{
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->_extensionAdminDir.DS.$this->_installScript);
				$ret = com_install();
				$ret .= ob_get_contents();
				ob_end_clean();
				if ($ret != '')
				{
					$this->message = $ret;
				}
			}
		}

		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		if (!$this->_copyInstallFile())
		{
			JError::raiseWarning(1, 'JInstallerComponent::install: '.JText::_('Could not copy setup file'));

			// Install failed, rollback changes
			$this->_rollback();
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method for components
	 *
	 * @access	public
	 * @param	int		$cid	The id of the component to uninstall
	 * @param	string	$option	The URL option
	 * @param	int		$client	The client id
	 * @return	mixed	Return value for uninstall method in component uninstall
	 * file
	 * @since	1.0
	 */
	function uninstall($id, $client = null)
	{

		/*
		 * Initialize variables
		 */
		$row = null;
		$retval = true;

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First order of business will be to load the component object table from the database.
		 * This should give us the necessary information to proceed.
		 */
		$row = & JTable::getInstance('component', $db);
		$row->load($id);

		/*
		 * Is the component we are trying to uninstall a core one?
		 * Because that is not a good idea...
		 */
		if ($row->iscore)
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: '.sprintf(JText::_('WARNCORECOMPONENT'), $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
			return false;
		}

		/*
		 * Get the admin and site paths for the component
		 */
		$this->_extensionAdminDir = JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option);
		$this->_extensionDir = JPath::clean(JPATH_SITE.DS.'components'.DS.$row->option);

		/*
		 * Find and load the XML install file for the component
		 */
		$this->_installDir = $this->_extensionAdminDir;
		if (!$this->_findInstallFile())
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: XML File invalid or not found');
			return false;
		}

		// Get the root node of the xml document
		$root = & $this->_xmldoc->documentElement;

		/*
		 * Now lets load the uninstall file if there is one and execute the uninstall
		 * function if it exists.
		 */
		$uninstallfileElement = & $root->getElementsByPath('uninstallfile', 1);
		if (!is_null($uninstallfileElement))
		{

			/*
			 * Element exists, does the file exist?
			 */
			if (!file_exists($this->_extensionAdminDir.$uninstallfileElement->getText()))
			{
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->_extensionAdminDir.$uninstallfileElement->getText());
				$ret = com_uninstall();
				$ret .= ob_get_contents();
				ob_end_clean();
				$this->message = $ret;
			}
		}

		/*
		 * Let's run the uninstall queries for the component
		 *    If backward compatibility is required - run queries in xml file
		 *    If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *    file for utf-8 support or non-utf support
		 */

		// start with backward compatibility <queries> tag
		$result = $this->_parseBackwardQueries('uninstall/queries');

		if ($result === false)
		{
			JError::raiseWarning(1, 'JInstallerComponent::uninstall: '.JText::_('SQL Error')." ".$db->stderr(true));
			$retval = false;
		} else
			if ($result === 0)
			{
				// no backward compatibility queries found - try for Joomla 1.5 type queries
				$utfresult = $this->_parseQueries("uninstall/sql/file", ($db->hasUTF() ? '4.1.2' : '3.2.0'));
				if ($utfresult === false)
				{
					JError::raiseWarning(1, 'JInstallerComponent::uninstall: '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
					$retval = false;
				}
			}

		/*
		 * Let's remove language files and media in the JROOT/images/ folder that are
		 * associated with the component we are uninstalling
		 */
		$this->_removeFiles('media');
		$this->_removeFiles('languages');
		$this->_removeFiles('administration/languages');

		/*
		 * Now we need to delete the installation directories.  This is the final step
		 * in uninstalling the component.
		 */
		if (trim($row->option))
		{

			// Delete the component site directory
			if (is_dir($this->_extensionDir))
			{
				if (!JFolder::delete($this->_extensionDir))
				{
					JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: '.JText::_('Unable to remove the component site directory'));
					$retval = false;
				}
			}

			// Delete the component admin directory
			if (is_dir($this->_extensionAdminDir))
			{
				if (!JFolder::delete($this->_extensionAdminDir))
				{
					JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: '.JText::_('Unable to remove the component admin directory'));
					$retval = false;
				}
			}

			/*
			 * Next, lets delete the submenus for the component.
			 */
			$sql = "DELETE " .
					"\nFROM #__components " .
					"\nWHERE parent = '".$row->id."'";

			$db->setQuery($sql);
			if (!$db->query())
			{
				JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: '.$db->stder(true));
				$retval = false;
			}

			/*
			 * Lastly, we will delete the component object
			 */
			if (!$row->delete($row->id))
			{
				JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: '.JText::_('Unable to delete the component from the database'));
				$retval = false;
			}
			return $retval;
		} else
		{
			// No component option defined... cannot delete what we don't know about
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::uninstall: Option field empty, cannot remove files');
			return false;
		}
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the component menu item
	 *
	 * @access	private
	 * @param	array	$arg	Installation step to rollback
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _rollback_menu($arg)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * Remove the entry from the #__components table
		 */
		$query = "DELETE " .
				"\nFROM `#__components` " .
				"\nWHERE id='".$arg['id']."'";

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
		$db = & $this->_db;

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
		$db_params = $this->_getParams();
		$db_enabled = 1;

		$query = "INSERT INTO #__components" .
				"\n VALUES( '', '$db_name', '$db_link', $db_menuid, $db_parent, '$db_admin_menu_link', '$db_admin_menu_alt', '$db_option', $db_ordering, '$db_admin_menu_img', $db_iscore, '$db_params', '$db_enabled' )";
		$db->setQuery($query);
		if (!$db->query())
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerComponent::_createParentMenu: '.$db->stder(true));
			return false;
		}
		$menuid = $db->insertid();
		return $menuid;
	}
}
?>