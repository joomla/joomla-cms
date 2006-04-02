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
* */

/**
 * Module installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.1
 */
class JInstallerModule extends JInstaller
{

	/**
	 * Custom install method
	 *
	 * @access public
	 * @param string $p_fromdir Directory from which to install the module
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install($p_fromdir)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'module'))
		{
			return false;
		}

		// Get the root node of the XML document
		$root = & $this->_xmldoc->documentElement;

		/*
		 * Get the client application target
		 */
		if ($client = $root->getAttribute('client'))
		{
			$clientVals = JApplicationHelper::getClientInfo($client, true);
			if ($clientVals === false)
			{
				JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('Unknown client type').' ['.$client.']');
				return false;
			}
			$basePath = $clientVals->path;
			$clientId = $clientVals->id;
		} else
		{
			/*
			 * No client attribute was found so we assume the site as the client
			 */
			$client = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		/*
		 * Set the extension name
		 */
		$e = & $root->getElementsByPath('name', 1);
		$this->_extensionName = $e->getText();

		/*
		 * Set the extension installation directory
		 */
		$e = & $root->getElementsByPath('files', 1);
		if (!is_null($e) && $e->hasChildNodes())
		{
			$files = & $e->childNodes;
			foreach ($files as $file)
			{
				if ($file->hasAttribute('module'))
				{
					$m = $file->getAttribute('module');
					break;
				}
			}
		}
		if (!empty ($m))
		{
			$this->_extensionDir = JPath::clean($basePath.DS.'modules'.DS.$m.DS);
		} else
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('No module file specified'));
			return false;
		}

		/*
		 * If the module directory already exists, then we will assume that the
		 * module is already installed or another module is using that
		 * directory.
		 */
		if (file_exists($this->_extensionDir))
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('Another module is already using directory').': "'.$this->_extensionDir.'"');
			return false;
		}

		/*
		 * If the module directory does not exists, lets create it
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
		 * Since we created the module directory and will want to remove it if
		 * we have to roll back the installation, lets add it to the
		 * installation step stack
		 */
		if ($created)
		{
			$this->_stepStack[] = array ('type' => 'folder', 'path' => $this->_extensionDir);
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->_parseFiles('files', 'module', JText::_('No file is marked as module file')) === false)
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('Failed to copy files to').' "'.$this->_extensionDir.'"');

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}

		/*
		 * Copy all the images and languages as well
		 */
		$this->_parseFiles('images');
		$this->_parseFiles('media');
		$this->_parseFiles('languages');
		$this->_parseFiles('administration/languages');

		/*
		 * Check to see if a module by the same name is already installed
		 */
		$query = "SELECT `id` " .
				"\nFROM `#__modules` " .
				"\nWHERE module = '".$this->_extensionSpecial."' " .
				"\nAND client_id = $clientId";

		$db->setQuery($query);
		if (!$db->Query())
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.$db->stderr(true));

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}
		$id = $db->loadResult();

		/*
		 * Was there a module already installed?
		 */
		if ($id)
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('Module').' "'.$this->_extensionName.'" '.JText::_('already exists!'));

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		} else
		{
			$row = & JTable::getInstance('module', $db);
			$row->title = $this->_extensionName;
			$row->ordering = 99;
			$row->position = 'left';
			$row->showtitle = 1;
			$row->iscore = 0;
			$row->access = $clientId == 1 ? 23 : 0;
			$row->client_id = $clientId;
			$row->module = $this->_extensionSpecial;
			$row->params = $this->_getParams();

			if (!$row->store())
			{
				JError::raiseWarning(1, 'JInstallerModule::install: '.$db->stderr(true));

				// Install failed, roll back changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a module item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->_stepStack[] = array ('type' => 'module', 'id' => $row->id);

			/*
			 * Time to create a menu entry for the module
			 */
			$query = "INSERT INTO `#__modules_menu` " .
					"\nVALUES ( $row->id, 0 )";
			$db->setQuery($query);
			if (!$db->query())
			{
				JError::raiseWarning(1, 'JInstallerModule::install: '.$db->stderr(true));

				// Install failed, roll back changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->_stepStack[] = array ('type' => 'menu', 'id' => $db->insertid());

		}

		/*
		 * Now, lets create the necessary module positions
		 */
		$templatePositions = & $root->getElementsByPath('install/positions', 1);
		if (!is_null($templatePositions))
		{
			$positions = $templatePositions->childNodes;
			foreach ($positions as $position)
			{

				$id = $this->_createTemplatePosition($position->getText());

				if ($id)
				{
					/*
					 * Since we have created a template positions item, we add it to the installation step stack
					 * so that if we have to rollback the changes we can undo it.
					 */
					$this->_stepStack[] = array ('type' => 'position', 'id' => $id);
				}
			}
		}

		/*
		 * Get the module description
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
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		if (!$this->_copyInstallFile(0))
		{
			JError::raiseWarning(1, 'JInstallerModule::install: '.JText::_('Could not copy setup file'));

			// Install failed, rollback changes
			$this->_rollback();
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access public
	 * @param int $cid The id of the module to uninstall
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall($id, $client = 0)
	{

		/*
		 * Initialize variables
		 */
		$row = null;
		$retval = true;

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First order of business will be to load the module object table from the database.
		 * This should give us the necessary information to proceed.
		 */
		$row = & JTable::getInstance('module', $db);
		$row->load($id);

		/*
		 * Is the component we are trying to uninstall a core one?
		 * Because that is not a good idea...
		 */
		if ($row->iscore)
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerModule::uninstall: '.sprintf(JText::_('WARNCORECOMPONENT'), $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
			return false;
		}

		/*
		 * Use the client id to determine which module path to use for the xml install file
		 */
		if (!$row->client_id)
		{
			$basepath = JPATH_SITE.DS.'modules'.DS.$row->module.DS;
		} else
		{
			$basepath = JPATH_ADMINISTRATOR.DS.'modules'.DS.$row->module.DS;
		}
		$this->_extensionDir = $basepath;

		// Get the path to the xml install file
		$xmlfile = $basepath.$row->module.'.xml';

		/*
		 * Lets delete all the module copies for the type we are uninstalling
		 */
		$query = "SELECT `id` " .
				"\nFROM `#__modules` " .
				"\nWHERE module = '".$row->module."' " .
				"\nAND client_id = '".$row->client_id."'";

		$db->setQuery($query);
		$modules = $db->loadResultArray();

		/*
		 * Do we have any module copies?
		 */
		if (count($modules))
		{
			$modID = implode(',', $modules);

			$query = "DELETE " .
					"\nFROM #__modules_menu " .
					"\nWHERE moduleid IN ('".$modID."')";

			$db->setQuery($query);
			if (!$db->query())
			{
				JError::raiseWarning(1, 'JInstallerModule::uninstall: '.$db->stderr(true));
				$retval = false;
			}
		}

		/*
		 * Now we will no longer need the module object, so lets delete it
		 */
		$row->delete($row->id);

		// Free up memory
		unset ($row);

		/*
		 * Now is time to load the xml file and process it
		 */
		if (file_exists($xmlfile))
		{
			$this->_xmldoc = & JFactory::getXMLParser();
			$this->_xmldoc->resolveErrors(true);

			if ($this->_xmldoc->loadXML($xmlfile, false, true))
			{

				/*
				 * Let's remove the files for the module
				 */
				if ($this->_removeFiles('files') === false)
				{
					JError::raiseWarning(1, 'JInstallerModule::uninstall: '.JText::_('Unable to remove all files'));
					$retval = false;
				}

				/*
				 * Remove other files
				 */
				$this->_removeFiles('images');
				$this->_removeFiles('media');
				$this->_removeFiles('languages');
				$this->_removeFiles('administration/languages');

				/*
				 * Remove module folder
				 */
				JFolder::delete($basepath);

			} else
			{
				JError::raiseWarning(1, 'JInstallerModule::uninstall: '.JText::_('Could not load XML file').' '.$xmlfile);
				$retval = false;
			}
		} else
		{
			JError::raiseWarning(1, 'JInstallerModule::uninstall: '.JText::_('File does not exist').' '.$xmlfile);
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Creates a new template position if it doesn't exist already
	 * 
	 * @static
	 * @param string $position Template position to create
	 * @return mixed Template position id (int) if a position was inserted or boolean false otherwise
	 * @since 1.1
	 */
	function _createTemplatePosition($position)
	{

		/*
		 * Get the global database connector object
		 */
		$db = $this->_db;

		// Initialize variable
		$retval = false;

		if ($position)
		{
			$db->setQuery("SELECT id " .
					"\nFROM #__template_positions " .
					"\nWHERE position = '$position'");
			$db->Query();
			if (!$db->getNumRows())
			{
				$db->setQuery("INSERT INTO #__template_positions " .
						"\nVALUES (0,'$position','')");
				if ($db->Query() !== false)
				{
					$retval = $db->insertid();
				}
			}
		}
		return $retval;
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the menu item
	 *
	 * @access private
	 * @param array $arg Installation step to rollback
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback_menu($arg)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * Remove the entry from the #__modules_menu table
		 */
		$query = "DELETE " .
				"\nFROM `#__modules_menu` " .
				"\nWHERE moduleid='".$arg['id']."'";

		$db->setQuery($query);

		return ($db->query() !== false);
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the template position item
	 *
	 * @access private
	 * @param array $arg Installation step to rollback
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback_position($arg)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * Remove the entry from the #__template_positions table
		 */
		$query = "DELETE " .
				"\nFROM `#__template_positions` " .
				"\nWHERE id='".$arg['id']."'";

		$db->setQuery($query);

		return ($db->query() !== false);
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the module item
	 *
	 * @access private
	 * @param array $arg Installation step to rollback
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback_module($arg)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * Remove the entry from the #__modules table
		 */
		$query = "DELETE " .
				"\nFROM `#__modules` " .
				"\nWHERE id='".$arg['id']."'";

		$db->setQuery($query);

		return ($db->query() !== false);
	}
}
?>