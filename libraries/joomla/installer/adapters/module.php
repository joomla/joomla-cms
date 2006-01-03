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
* */

/**
 * Module installer
 *
 * @package 	Joomla.Framework
 * @subpackage 	Installer
 * @since 1.1
 */
class JInstallerModule extends JInstaller {

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
	 * @param string $p_fromdir Directory from which to install the module
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install($p_fromdir) {
		global $mainframe;

		// Get the database connector object
		$db = & $mainframe->getDBO();

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'module')) {
			return false;
		}

		$xmlDoc = $this->xmlDoc();
		$jinstall = & $xmlDoc->documentElement;

		/*
		 * Get the client value
		 */
		$client = null;
		if ($jinstall->getAttribute('client')) {
			$validClients = array ('administrator');
			if (!in_array($jinstall->getAttribute('client'), $validClients)) {
				$this->setError(1, JText::_('Unknown client type').' ['.$jinstall->getAttribute('client').']');
				return false;
			}
			$client = 'admin';
		}

		// Set some necessary variables
		$e = & $jinstall->getElementsByPath('name', 1);
		$this->elementName($e->getText());
		$this->elementDir(JPath::clean(JPATH_SITE. ($client == 'admin' ? DS.'administrator' : '').DS.'modules'.DS));

		/*
		 * Copy all the necessary files
		 */
		if ($this->parseFiles('files', 'module', JText::_('No file is marked as module file')) === false) {
			return false;
		}

		/*
		 * Copy all the images and languages as well
		 */
		$this->parseFiles('images');
		//$this->parseFiles( 'languages' );

		$client_id = intval($client == 'admin');

		/*
		 * Check to see if a module by the same name is already installed
		 */
		$query = 	"SELECT `id` " .
					"\nFROM `#__modules` " .
					"\nWHERE module = '".$this->elementSpecial()."' " .
					"\nAND client_id = $client_id";

		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError(1, JText::_('SQL error').': '.$database->stderr(true));

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}

		$id = $db->loadResult();

		if (!$id) {
			$row = new JModuleModel($db);
			$row->title = $this->elementName();
			$row->ordering = 99;
			$row->position = 'left';
			$row->showtitle = 1;
			$row->iscore = 0;
			$row->access = $client == 'admin' ? 99 : 0;
			$row->client_id = $client_id;
			$row->module = $this->elementSpecial();

			$row->store();

			/*
			 * Since we have created a module item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$step = array ('type' => 'module', 'id' => $row->_db->insertid());
			$this->i_stepstack[] = $step;

			$query = "INSERT INTO `#__modules_menu` "."\nVALUES ( $row->id, 0 )";

			$db->setQuery($query);
			if (!$db->query()) {
				$this->setError(1, JText::_('SQL error').': '.$database->stderr(true));

				// Install failed, roll back changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$step = array ('type' => 'menu', 'id' => $row->id);
			$this->i_stepstack[] = $step;

		} else {
			$this->setError(1, JText::_('Module').' "'.$this->elementName().'" '.JText::_('already exists!'));

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}

		/*
		 * Next, lets set the description for the module
		 */
		if ($e = & $jinstall->getElementsByPath('description', 1)) {
			$this->setError(0, $this->elementName().'<p>'.$e->getText().'</p>');
		}

		/*
		 * Now, lets create the necessary module positions
		 */
		$template_positions = & $jinstall->getElementsByPath('install/positions', 1);
		if (!is_null($template_positions)) {
			$positions = $template_positions->childNodes;
			foreach ($positions as $position) {
				$this->createTemplatePosition($position);
			}
		}

		/*
		 * Now lets check and see if we have any database queries, if so lets run them.
		 */
		$query_element = & $jinstall->getElementsByPath('install/queries', 1);
		if (!is_null($query_element)) {
			$queries = $query_element->childNodes;
			foreach ($queries as $query) {
				$db->setQuery($query->getText());
				if (!$db->query()) {
					$this->setError(1, JText::_('SQL Error')." ".$db->stderr(true));

					// Install failed, roll back changes
					$this->_rollback();
					return false;
				}
			}
		}

		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		if (!$this->copySetupFile('front')) {
			$this->setError(1, JText::_('Could not copy setup file'));

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
	 * @param string $option The URL option
	 * @param int $client The client id
	 * @return mixed Return value for uninstall method in component uninstall file
	 * @since 1.1
	 */
	function uninstall($id, $option, $client = 0) {
		global $mainframe;

		// Initialize variables
		$id = intval($id);

		// Get database connector object
		$db = & $mainframe->getDBO();

		// Load the module we want to uninstall
		$row = new JModuleModel($db);
		$row->load($id);

		/*
		 * Is the module a core module?  If so we can't uninstall it.
		 */
		if ($row->iscore) {
			HTML_installer::showInstallMessage(sprintf(JText::_('WARNCOREMODULE'), $row->title).'<br />'.JText::_('WARNCORECOMPONENT2'), JText::_('Uninstall - error'), $this->returnTo($option, 'module', $row->client_id ? '' : 'admin'));
			exit ();
		}

		/*
		 * This stuff seems as if it is unnecesary.... why can't we just delete the module with the
		 * id that we chose to delete?
		 *
				$query = "SELECT `id` " .
						"\nFROM `#__modules` " .
						"\nWHERE module = '". $row->module ."' " .
						"\nAND client_id = '". $row->client_id ."'";

				$db->setQuery( $query );
				$modules = $db->loadResultArray();

				if (count( $modules )) {
		            $modID = implode( ',', $modules );

					$query = "DELETE " .
							"\nFROM #__modules_menu " .
							"\nWHERE moduleid IN ('". $modID ."')";

					$db->setQuery( $query );
					if (!$db->query()) {
						$msg = $db->stderr;
						die( $msg );
					}

		    		$query = "DELETE " .
		    				"\nFROM #__modules " .
		    				"\nWHERE module = '". $row->module ."' " .
							"\nAND client_id = '". $row->client_id ."'";

		    		$db->setQuery( $query );
		    		if (!$db->query()) {
		    			$msg = $db->stderr;
		    			die( $msg );
		    		}
		*
		*
		*/

		/*
		 * Use the client id to determine which module path to use for the xml install file
		 */
		if (!$row->client_id) {
			$basepath = JPATH_SITE.DS.'modules'.DS;
		} else {
			$basepath = JPATH_ADMINISTRATOR.DS.'modules'.DS;
		}

		// Get the path to the xml install file
		$xmlfile = $basepath.$row->module.'.xml';

		/*
		 * Now we will no longer need the module object, so lets delete it
		 */
		$row->delete($row->id);

		/*
		 * Now is time to process the xml install file stuff...
		 */
		if (file_exists($xmlfile)) {
			$this->i_xmldoc = & JFactory::getXMLParser();
			$this->i_xmldoc->resolveErrors(true);

			if ($this->i_xmldoc->loadXML($xmlfile, false, true)) {
				$jinstall = & $this->i_xmldoc->documentElement;

				// Lets remove the installed files
				$files_element = & $jinstall->getElementsByPath('files', 1);
				if (!is_null($files_element)) {
					$files = $files_element->childNodes;
					foreach ($files as $file) {

						$filename = $file->getText();
						if (file_exists($basepath.$filename)) {

							$subpath = dirname($filename);
							if ($subpath <> '' && $subpath <> '.' && $subpath <> '..') {
								echo '<br />'.JText::_('Deleting').': '.$basepath.$subpath;
								$result = JFolder::delete($basepath.$subpath.DS);
							} else {
								echo '<br />'.JText::_('Deleting').': '.$basepath.$filename;
								$result = JFile::delete($basepath.$filename, false);
							}
							echo intval($result);
						}
					}

					// remove XML file from front
					echo JText::_('Deleting XML File').": ".$xmlfile;
					JFile::delete(JPath::clean($xmlfile, false));
					return true;
				}
			}
		}
	}

	/**
	 * Roll back the installation
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
		$db = & $mainframe->getDBO();

		while ($step != null) {

			switch ($step['type']) {
				case 'file':
					// remove the file
					JFile::delete($step['path']);
					break;

				case 'folder' :
					// remove the folder
					JFolder::delete($step['path']);
					break;

				case 'module' :
					// remove the module item
					$com = new JModuleModel($db);
					$com->delete($step['id']);
					break;

				case 'menu' :
					// remove the module menu item
					$query = 	"DELETE " .
								"\nFROM `#__modules_menu` " .
								"\nWHERE moduleid='".$step['id']."'";

					$db->setQuery( $query );
					$db->query();
					break;

				case 'query' :
					// placeholder in case this is necessary in the future
					break;

				default :
					// do nothing
					break;
			}

			// Get the next step
			$step = array_pop($this->i_stepstack);
		}

		return $retval;
	}
}
?>
