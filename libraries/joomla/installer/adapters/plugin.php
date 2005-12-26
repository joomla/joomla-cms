<?php
/**
 * @version $Id: mambot.php 1479 2005-12-20 03:47:14Z Jinx $
 * @package JoomlaFramework
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Plugin installer
 *
 * @package JoomlaFramework
 * @subpackage Installer
 * @since 1.1
 */
class JInstallerPlugin extends JInstaller {

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
	 * @param string $p_fromdir Directory from which to install the plugin
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
		if (!$this->preInstallCheck($p_fromdir, 'plugin')) {
			return false;
		}

		$xmlDoc = $this->xmlDoc();
		$jinstall = & $xmlDoc->documentElement;

		// Set some necessary variables
		$e = & $jinstall->getElementsByPath('name', 1);
		$this->elementName($e->getText());
		$folder = $jinstall->getAttribute('group');
		$this->elementDir(JPath::clean(JPATH_SITE.DS.'plugins'.DS.$folder));


		/*
		 * If the plugin directory does not exist, lets create it
		 */
		if (!file_exists($this->elementDir()) && !JFolder::create($this->elementDir())) {
			$this->setError(1, JText::_('Failed to create directory').' "'.$this->elementDir().'"');
			return false;
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->parseFiles('files', 'plugin', JText::_('No file is marked as mambot file')) === false) {
			return false;
		}

		/*
		 * Check to make sure a plugin by the same name is not already installed
		 */
		$query = 	"SELECT `id` " .
					"\nFROM `#__plugins` " .
					"\nWHERE element = '".$this->elementName()."'";

		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError(1, JText::_('SQL error').': '.$db->stderr(true));
			return false;
		}

		// If value is loaded then a plugin with the same name DOES exist
		$id = $db->loadResult();

		if (!$id) {
			$row = new JPluginModel($db);
			$row->name = $this->elementName();
			$row->ordering = 0;
			$row->folder = $folder;
			$row->iscore = 0;
			$row->access = 0;
			$row->client_id = 0;
			$row->element = $this->elementSpecial();

			if ($folder == 'editors') {
				$row->published = 1;
			}

			if (!$row->store()) {
				$this->setError(1, JText::_('SQL error').': '.$row->getError());

				// Install failed, rollback any changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a plugin item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$step = array ('type' => 'plugin', 'id' => $row->_db->insertid());
			$this->i_stepstack[] = $step;

		} else {
			$this->setError(1, JText::_('Plugin').' "'.$this->elementName().'" '.JText::_('already exists!'));

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Next, lets set the description for the plugin
		 */
		if ($e = & $jinstall->getElementsByPath('description', 1)) {
			$this->setError(0, $this->elementName().'<p>'.$e->getText().'</p>');
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
	 * @param int $cid The id of the plugin to uninstall
	 * @param string $option The URL option
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall($id, $option, $client = 0) {
		global $mainframe;

		// Get database connector object
		$db =& $mainframe->getDBO();

		$id = intval($id);

		// Load the plugin we want to uninstall
		$row = new JPluginModel($db);
		$row->load($id);

		/*
		 * Is the module a core plugin?  If so we can't uninstall it.
		 */
		if ($row->iscore) {
			HTML_installer::showInstallMessage(sprintf(JText::_('WARNCOREELEMENT'), $row->name).'<br />'.JText::_('WARNCORECOMPONENT2'), JText::_('Uninstall - error'), $this->returnTo($option, 'plugin', $client));
			exit ();
		}

		if ($row->id == null) {
			HTML_installer::showInstallMessage('Invalid object id', JText::_('Uninstall - error'), $this->returnTo($option, 'plugin', $client));
			exit ();
		}

		/*
		 * Get the plugin folder so we can properly build the plugin path
		 */
		if (trim($row->folder) == '') {
			HTML_installer::showInstallMessage(JText::_('Folder field empty, cannot remove files'), JText::_('Uninstall - error'), $this->returnTo($option, 'plugin', $client));
			exit ();
		}

		/*
		 * Use the client id to determine which module path to use for the xml install file
		 */
		if (!$row->client_id) {
			$basepath = JPATH_SITE.DS.'plugins'.DS.$row->folder.DS;
		} else {
			$basepath = JPATH_ADMINISTRATOR.DS.'plugins'.DS.$row->folder.DS;
		}

		$xmlfile = $basepath.$row->element.'.xml';
		$folder = $row->folder;

		/*
		 * Now we will no longer need the plugin object, so lets delete it
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
							if ($subpath != '' && $subpath != '.' && $subpath != '..') {
								echo '<br />'.JText::_('Deleting').': '.$basepath.$subpath;
								$result = JFolder::delete(JPath::clean($basepath.$subpath.DS));
							} else {
								echo '<br />'.JText::_('Deleting').': '.$basepath.$filename;
								$result = JFile::delete(JPath::clean($basepath.$filename, false));
							}
							echo intval($result);
						}
					}

					/*
					 * Now lets remove the installation file
					 */
					echo JText::_('Deleting XML File').": ".$xmlfile;
					JFile::delete(JPath::clean($xmlfile, false));

					/*
					 * Remove plugin folder if empty and not a system folder
					 */
					$sysFolders = array ('content', 'search', 'auth');
					if (!in_array($folder, $sysFolders)) {
						// If folder is no empty, lets delete it
						$list = array_merge(JFolder::files($basepath), JFolder::folders($basepath));
						if (count($list) < 1) {
							JFolder::delete($basepath);
						}
					}
				}
			}
		}
		return true;
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

				case 'plugin' :
					// remove the plugin item
					$p = new JPluginModel($db);
					$p->delete($step['id']);
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