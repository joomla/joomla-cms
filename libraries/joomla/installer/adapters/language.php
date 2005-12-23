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
* Language installer
*
* @package Joomla
* @subpackage Installer
*/
class JInstallerLanguage extends JInstaller
{
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
	 * @param string $p_fromdir Directory from which to install the language
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install( $p_fromdir ) {
		global $mainframe;

		// Get database connector object
		$db =& $mainframe->getDBO();

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck( $p_fromdir, 'language' )) {
			return false;
		}

		$xmlDoc = $this->xmlDoc();
		$jinstall = &$xmlDoc->documentElement;

		// Set some necessary variables
		$client = $jinstall->getAttribute( 'client' );
		$e = &$jinstall->getElementsByPath( 'name', 1);
		$this->elementName($e->getText());
		$e = &$jinstall->getElementsByPath( 'metadata/tag', 1);
		$folder = $e->getText();
		if ($client == 'administrator') {
			$this->elementDir( JPath::clean( JPATH_ADMINISTRATOR . DS ."language". DS .$folder ) );
		} else {
			$this->elementDir( JPath::clean( JPATH_SITE . DS ."language". DS .$folder ) );
		}

		/*
		 * If the language directory does not exist, lets create it
		 */
		if (!file_exists($this->elementDir()) && !JFolder :: create($this->elementDir())) {
			$this->setError(1, JText :: _('Failed to create directory').' "'.$this->elementDir().'"');
			return false;
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->parseFiles( 'files', 'language' ) === false) {
			return false;
		}

		/*
		 * Next, lets set the description for the language
		 */
		if ($e = &$jinstall->getElementsByPath( 'description', 1 )) {
			$this->setError( 0, $this->elementName() . '<p>' . $e->getText() . '</p>' );
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
	 * @param int $cid The id of the language to uninstall
	 * @param string $option The URL option
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall( $id, $option, $client=0 ) {
		$id = str_replace( array( '\\', '/' ), '', $id );

		$basepath = JPATH_SITE . DS .'language'. DS;
		$xmlfile = $basepath . $id . '.xml';

		// see if there is an xml install file, must be same name as element
		if (file_exists( $xmlfile )) {
			$this->i_xmldoc =& JFactory::getXMLParser();
			$this->i_xmldoc->resolveErrors( true );

			if ($this->i_xmldoc->loadXML( $xmlfile, false, true )) {
				$jinstall =& $this->i_xmldoc->documentElement;

				/*
				 * Get the metadata tag which also seves as the language subdirectory
				 */
				$folder =& $jinstall->getElementsByPath( 'metadata/tag', 1);
				$basepath = $basepath . $folder->getText() . DS;

				/*
				 * Get the files element
				 */
				$files_element =& $jinstall->getElementsByPath( 'files', 1 );

				if (!is_null( $files_element )) {
					$files = $files_element->childNodes;
					foreach ($files as $file) {
						// delete the files
						$filename = $file->getText();
						echo $filename;
						if (file_exists( $basepath . $filename )) {
							echo '<br />'. JText::_( 'Deleting' ) .': '. $basepath . $filename;
							$result = JFile::delete( $basepath . $filename );
						}
						echo intval( $result );
					}
				}
			}
		} else {
			HTML_installer::showInstallMessage( JText::_( 'Language id empty, cannot remove files' ), JText::_( 'Uninstall - error' ), $this->returnTo( $option, 'language', $client ) );
			exit();
		}

		// remove XML file from front
		JFile::delete( $xmlfile );

		return true;
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
	function returnTo( $option, $element, $client ) {
		return "index2.php?option=com_languages";
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
					JFolder :: delete($step['path']);
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