<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
* @package Joomla
* @subpackage Installer
*/
class JInstallerComponent extends JInstaller 
{
	var $i_componentadmindir 	= '';
	var $i_hasinstallfile 		= false;
	var $i_installfile 			= '';
	
	/**
	 * Constructor
	 *
	 * @access protected
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Custom install method
	 * 
	 * @access public
	 * @param string $p_fromdir Directory from which to install the component
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install($p_fromdir) {
		global $mainframe;

		// Get database connector object
		$db =& $mainframe->getDBO();
		
		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck( $p_fromdir, 'component' )) {
			return false;
		}

		// aje moved down to here. ??  seemed to be some referencing problems
		$xmlDoc 	= $this->xmlDoc();
		$jinstall = &$xmlDoc->documentElement;

		// Set some necessary variables
		$e = &$jinstall->getElementsByPath('name', 1);
		$this->elementName($e->getText());
		$this->elementDir( JPath::clean( JPATH_SITE . DS ."components". DS
			. strtolower("com_" . str_replace(" ","",$this->elementName())) . DS )
		);
		$this->componentAdminDir( JPath::clean( JPATH_SITE . DS."administrator".DS."components".DS
			. strtolower( "com_" . str_replace( " ","",$this->elementName() ) ) )
		);

		/*
		 * If the component directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */
		if (file_exists($this->elementDir())) {
			$this->setError( 1, JText::_( 'Another component is already using directory' ) .': "' . $this->elementDir() . '"' );
			return false;
		}

		/*
		 * If the component directory does not exists, lets create it
		 */
		if(!file_exists($this->elementDir()) && !JFolder::create($this->elementDir())) {
			$this->setError( 1, JText::_( 'Failed to create directory' ) .' "' . $this->elementDir() . '"' );
			return false;
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back 
		 * the installation, lets add it to the installation step stack
		 */
		$step = array('type' => 'folder', 'path' => $this->elementDir());
		$this->i_stepstack[] = $step;
		
		/*
		 * If the component admin directory does not exist, lets create it as well
		 */
		if(!file_exists($this->componentAdminDir()) && !JFolder::create($this->componentAdminDir())) {
			$this->setError( 1, JText::_( 'Failed to create directory' ) .' "' . $this->componentAdminDir() . '"' );

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, lets add it to the installation step stack
		 */
		$step = array('type' => 'folder', 'path' => $this->componentAdminDir());
		$this->i_stepstack[] = $step;
		
		// Find files to copy
		if ($this->parseFiles( 'files' ) === false) {
			
			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}
		$this->parseFiles( 'images' );
		$this->parseFiles( 'languages' );
		$this->parseFiles( 'administration/languages' );
		$this->parseFiles( 'administration/files','','',1 );
		$this->parseFiles( 'administration/images','','',1 );

		/*
		 * Now lets check and see if we have any database queries, if so lets run them.
		 */
		$query_element = &$jinstall->getElementsByPath('install/queries', 1);
		if (!is_null($query_element)) {
			$queries = $query_element->childNodes;
			foreach($queries as $query) {
				
				$db->setQuery( $query->getText());
				if (!$db->query()) {
					$this->setError( 1, JText::_( 'SQL Error' ) ." " . $db->stderr( true ) );
					
					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}
		}

		/*
		 * If there is an install file, lets copy it.
		 */
		$installfile_elemet = &$jinstall->getElementsByPath('installfile', 1);
		if (!is_null($installfile_elemet)) {
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->componentAdminDir().$installfile_elemet->getText())) {
				if(!$this->copyFiles($this->installDir(), $this->componentAdminDir(), array($installfile_elemet->getText())))  			{
					$this->setError( 1, JText::_( 'Could not copy PHP install file.' ) );
					
					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}
			$this->hasInstallfile(true);
			$this->installFile($installfile_elemet->getText());
		}
		
		/*
		 * If there is an uninstall file, lets copy it too.
		 */
		$uninstallfile_elemet = &$jinstall->getElementsByPath('uninstallfile',1);
		if(!is_null($uninstallfile_elemet)) {
			// Make sure it hasn't already been copied (this would be an error in the xml install file)
			if (!file_exists($this->componentAdminDir().$uninstallfile_elemet->getText())) {
				if(!$this->copyFiles($this->installDir(), $this->componentAdminDir(), array($uninstallfile_elemet->getText()))) {
					$this->setError( 1, JText::_( 'Could not copy PHP uninstall file.' ) );
					
					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}
			}
		}

		/*
		 * Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		 */
		$adminmenu_element = &$jinstall->getElementsByPath('administration/menu',1);
		if(!is_null($adminmenu_element)) {
			
			// Initialize some variables
			$adminsubmenu_element	= &$jinstall->getElementsByPath('administration/submenu',1);
			$com_name				= strtolower("com_" . str_replace(" ","",$this->elementName()));
			$com_admin_menuname		= $adminmenu_element->getText();

			if(!is_null($adminsubmenu_element)) {
				
				// Lets create the component root menu
				$com_admin_menu_id	= $this->createParentMenu($com_admin_menuname,$com_name);
				if($com_admin_menu_id === false) {
					
					// Install failed, rollback changes
					$this->_rollback();
					return false;
				}

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$step = array('type' => 'menu', 'id' => $com_admin_menu_id);
				$this->i_stepstack[] = $step;

				// Get the submenus array
				$com_admin_submenus = $adminsubmenu_element->childNodes;

				// Lets build the submenus
				$submenuordering = 0;
				foreach($com_admin_submenus as $admin_submenu) {
					$com = new mosComponent( $db );
					$com->name		= $admin_submenu->getText();
					$com->link		= '';
					$com->menuid	= 0;
					$com->parent	= $com_admin_menu_id;
					$com->iscore	= 0;

					if ( $admin_submenu->getAttribute("act")) {
						$com->admin_menu_link = "option=$com_name&act=" . $admin_submenu->getAttribute("act");
					}
					else if ($admin_submenu->getAttribute("task")) {
						$com->admin_menu_link = "option=$com_name&task=" . $admin_submenu->getAttribute("task");
					}
					else if ($admin_submenu->getAttribute("link")) {
						$com->admin_menu_link = $admin_submenu->getAttribute("link");
					}
					else {
						$com->admin_menu_link = "option=$com_name";
					}
					
					$com->admin_menu_alt = $admin_submenu->getText();
					$com->option = $com_name;
					$com->ordering = $submenuordering++;
					$com->admin_menu_img = "js/ThemeOffice/component.png";

					if (!$com->store()) {
						$this->setError( 1, $db->stderr( true ) );
						
						// Install failed, rollback changes
						$this->_rollback();
						return false;
					}

					/*
					 * Since we have created a menu item, we add it to the installation step stack
					 * so that if we have to rollback the changes we can undo it.
					 */
					$step = array('type' => 'menu', 'id' => $com->_db->insertid());
					$this->i_stepstack[] = $step;
				}
			} else {
				
				// No submenus, just create the component root menu item
				$menuid = $this->createParentMenu($com_admin_menuname,$com_name);

				/*
				 * Since we have created a menu item, we add it to the installation step stack
				 * so that if we have to rollback the changes we can undo it.
				 */
				$step = array('type' => 'menu', 'id' => $menuid);
				$this->i_stepstack[] = $step;
			}
		}

		// Initialize variables
		$desc= null;
		/*
		 * Get the component description
		 */
		if ($e = &$jinstall->getElementsByPath( 'description', 1 )) {
			$desc = $this->elementName() . '<p>' . $e->getText() . '</p>';
		}
		$this->setError( 0, $desc );

		/*
		 * If we have an install file, lets include it, execute the custom install method, and
		 * append the return value from the custom install method to the installation message.
		 */
		if ($this->hasInstallfile()) {
			if (is_file($this->componentAdminDir() . DS . $this->installFile())) {
				require_once($this->componentAdminDir() . DS . $this->installFile());
				$ret = com_install();
				if ($ret != '') {
					$this->setError( 0, $desc . $ret );
				}
			}
		}
		
		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		 if (!$this->copySetupFile()) {
		 	$this->setError( 1, JText::_( 'Could not copy setup file' ));
		 	
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
	 * @param int $cid The id of the component to uninstall
	 * @param string $option The URL option
	 * @param int $client The client id
	 * @return mixed Return value for uninstall method in component uninstall file
	 * @since 1.1
	 */
	function uninstall( $cid, $option, $client=0 ) {
		global $mainframe;
		
		// Initialize variables
		$uninstallret = false;
		$row = null;
		
		// Get database connector object
		$db = & $mainframe->getDBO();
		
		/*
		 * First order of business will be to load the component object model from the database.
		 * This should give us the necessary information to proceed.
		 */
		$row = new mosComponent($db);
		$row->load($cid);

		/*
		 * Is the component we are trying to uninstall a core one? 
		 * Because that is not a good idea...
		 */
		if ($row->iscore) {
            HTML_installer::showInstallMessage( sprintf( JText::_( 'WARNCORECOMPONENT' ), $row->name ) ."<br />". JText::_( 'WARNCORECOMPONENT2' ), JText::_( 'Uninstall - error' ),
				$this->returnTo( $option, 'component', $client ) );
			exit();
		}

		/*
		 * Next, lets delete the submenus for the component.
		 */
		$sql = 	"DELETE " .
				"\nFROM #__components " .
				"\nWHERE parent = $row->id";
		
		$db->setQuery($sql);
		
		if (!$db->query()) {
			HTML_installer::showInstallMessage($db->stderr(true),JText::_( 'Uninstall - error' ),
			$this->returnTo( $option, 'component', $client ) );
			exit();
		}

		/*
		 * Now lets try to find the uninstall file and execute the uninstall function if it exists.
		 *  -	Read the files in the installation directory and look for the one with "uninstall" in 
		 * 		name.
		 */
		$uninstallfiles = JFolder::files( JPATH_AMINISTRATOR . DS .'components'. DS .$row->option, 'uninstall' );
		if (count( $uninstallfiles ) > 0) {
			$uninstall_file = $uninstallfiles[0];
			if(JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option . DS .$uninstall_file)) {
				require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option .DS.$uninstall_file );
				$uninstallret = com_uninstall();
			}
		}

		/*
		 * Now we need to find the XML installation file.  It has all the queries we need for uninstalling
		 * the component. If we find it, run the uninstall queries.
		 */
		$installfiles = JFolder::files( JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option, '.xml$');
		if (count($installfiles) > 0) {

			// Initialize variables
			$found = false;
			
			// Check each xml file found to see if it is an installation file
			foreach ($installfiles as $file) {
				$xmlDoc =& JFactory::getXMLParser();
				$xmlDoc->resolveErrors( true );
				if (!$xmlDoc->loadXML( JPATH_ADMINISTRATOR.DS."components".DS.$row->option . DS . $file, false, true )) {
					return false;
				}
				$root = &$xmlDoc->documentElement;

				if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'jinstall') {
					// Free up DOMIT parser memory
					unset($xmlDoc);
					continue;
				}
				$found = true;

				// Ahh, we found the installation file, lets run the uninstall queries.
				$query_element = &$root->getElementsbyPath( 'uninstall/queries', 1 );
				if(!is_null($query_element)) {
					
					$queries = $query_element->childNodes;
					foreach($queries as $query)	{
						
						$db->setQuery( $query->getText());
						if (!$db->query()) {
							HTML_installer::showInstallMessage($db->stderr(true),JText::_( 'Uninstall - error' ),
								$this->returnTo( $option, 'component', $client ) );
							exit();
						}
					}
				}
				break;
			}
			// Didn't find the installation file...
			if(!$found) {
				HTML_installer::showInstallMessage('XML File invalid or not found',JText::_( 'Uninstall - error' ),
					$this->returnTo( $option, 'component', $client ) );
				exit();
			}
		// Couldn't find ANY xml files...
		} else {
			/*
			 * HTML_installer::showInstallMessage( 'Could not find XML Setup file in '.JPATH_ADMINISTRATOR.'/components/'.$row->option,
			 * 	'Uninstall -  error', $option, 'component' );
			 * exit();
			 */
		}

		/*
		 * Now we need to delete the installation directories.  This is the final step in uninstalling
		 * the component.
		 */
		if (trim( $row->option )) {
			
			// Initialize variables
			$result = false;
			
			// Delete the component admin directory
			$path = JPath::clean( JPATH_ADMINISTRATOR.DS.'components'. DS . $row->option, true );
			if (is_dir( $path )) {
				$result |= JFolder::delete( $path );
			}
			
			// Delete the component site directory
			$path = JPath::clean( JPATH_SITE.DS.'components'.DS.$row->option, true );
			if (is_dir( $path )) {
				$result |= JFolder::delete( $path );
			}
			
			/*
			 * Lastly, we will delete the component object
			 */
			 $row->delete($row->id);
			 
			return $result;
		} else {
			// No component option defined... cannot delete what we don't know about
			HTML_installer::showInstallMessage( 'Option field empty, cannot remove files', JText::_( 'Uninstall - error' ), $option,'component');
			exit();
		}

		return $uninstallret;
	}

	/**
	 * Roll back the component installation
	 * 
	 * @access private
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback() {
		global $mainframe;
		
		// Initialize variables
		$retval = false;
		$step = array_pop($this->i_stepstack);
		
		// Get database connector object
		$db =& $mainframe->getDBO();
		
		while ($step != null) {

			switch ($step['type']) {
				case 'file':
					// remove the file
					JFile::delete($step['path']);
					break;
				
				case 'folder':
					// remove the folder
					JFolder::delete($step['path']);
					break;
				
				case 'menu':
					// remove the menu item
					$com = new mosComponent( $db );
					$com->delete($step['id']);
					break;
				
				case 'query':
					// placeholder in case this is necessary in the future
					break;
				
				default:
					// do nothing
					break;
			}			
			
			// Get the next step
			$step = array_pop($this->i_stepstack);
		}
		
		return $retval;
	}

	/**
	 * Method to create a menu entry for a component
	 * 
	 * @access private
	 * @param string $_menuname
	 * @param string $_comname
	 * @param string $_image
	 * @return int Id of the created menu entry
	 * @since 1.1
	 */
	function createParentMenu($_menuname,$_comname, $_image = "js/ThemeOffice/component.png") {
		global $mainframe;
		
		// Get database connector object
		$db =& $mainframe->getDBO();
		
		$db_name			= $_menuname;
		$db_link			= "option=$_comname";
		$db_menuid			= 0;
		$db_parent			= 0;
		$db_admin_menu_link	= "option=$_comname";
		$db_admin_menu_alt	= $_menuname;
		$db_option			= $_comname;
		$db_ordering		= 0;
		$db_admin_menu_img	= $_image;
		$db_iscore			= 0;
		$db_params			= '';

		$query = "INSERT INTO #__components"
		. "\n VALUES( '', '$db_name', '$db_link', $db_menuid, $db_parent, '$db_admin_menu_link', '$db_admin_menu_alt', '$db_option', $db_ordering, '$db_admin_menu_img', $db_iscore, '' )";
		$db->setQuery( $query );
		if(!$db->query())
		{
			$this->setError( 1, $db->stderr( true ) );
			return false;
		}
		$menuid = $db->insertid();
		return $menuid;
	}

	/**
	 * Get the component admin directory (Set it if path parameter is not null)
	 * 
	 * @access private
	 * @param string $p_dirname Path name for the component admin directory [Optional]
	 * @return string The path of the component admin directory
	 * @since 1.1
	 */
	function componentAdminDir($p_dirname = null) {
		if(!is_null($p_dirname)) {
			$this->i_componentadmindir = JPath::clean($p_dirname);
		}
		return $this->i_componentadmindir;
	}
}
?>
