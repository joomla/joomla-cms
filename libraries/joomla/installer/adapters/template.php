<?php
/**
* @version $Id$
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
 * Template installer
 *
 * @package JoomlaFramework
 * @subpackage Installer
 * @since 1.1
 */
class JInstallerTemplate extends JInstaller {

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
	 * @param string $p_fromdir Directory from which to install the template
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
		if (!$this->preInstallCheck($p_fromdir, 'template')) {
			return false;
		}

		$xmlDoc = & $this->xmlDoc();
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
		$this->elementDir(JPath::clean(JPATH_SITE. ($client == 'admin' ? DS.'administrator' : '').DS.'templates'.DS.strtolower(str_replace(" ", "_", $this->elementName()))));

		/*
		 * If the template directory does not exists, lets create it
		 */
		if (!file_exists($this->elementDir()) && !JFolder::create($this->elementDir())) {
			$this->setError(1, JText::_('Failed to create directory').' "'.$this->elementDir().'"');
			return false;
		}

		/*
		 * Copy all necessary files
		 */
		if ($this->parseFiles('files') === false) {
			return false;
		}
		if ($this->parseFiles('images') === false) {
			return false;
		}
		if ($this->parseFiles('css') === false) {
			return false;
		}
		if ($this->parseFiles('media') === false) {
			return false;
		}

		/*
		 * Get the template description
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
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		 if (!$this->copySetupFile('front')) {
		 	$this->setError( 1, JText::_( 'Could not copy setup file' ));
		 	return false;
		 }
		return true;
	}


	/**
	 * Custom uninstall method
	 *
	 * @access public
	 * @param int $id The id of the template to uninstall
	 * @param string $option The URL option
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall($id, $option, $client = 0) {
		global $mainframe;

		/*
		 * Build the template path
		 */
		$path = JPATH_SITE. ($client == 'admin' ? DS.'administrator' : '').DS.'templates'.DS.$id;

		/*
		 * Delete the template directory
		 */
		$id = str_replace('..', '', $id);
		if (trim($id)) {
			if (JFolder::exists($path)) {
				return JFolder::delete(JPath::clean($path));
			} else {
				HTML_installer::showInstallMessage(JText::_('Directory does not exist, cannot remove files'), JText::_('Uninstall - error'), $this->returnTo($option, 'template', $client));
			}
		} else {
			HTML_installer::showInstallMessage(JText::_('Template id is empty, cannot remove files'), JText::_('Uninstall - error'), $this->returnTo($option, 'template', $client));
			exit ();
		}
	}

	/**
	 * Overridden returnTo method
	 *
	 * @access public
	 * @param string $option
	 * @param string $element
	 * @param int $client
	 * @return string URL to return to
	 * @since 1.1
	 */
	function returnTo($option, $element, $client) {
		return "index2.php?option=com_templates&client=$client";
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
