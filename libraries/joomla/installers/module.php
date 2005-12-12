<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
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
* @package Joomla
*/
class mosInstallerModule extends mosInstaller {
	/**
	* Custom install method
	* @param boolean True if installing from directory
	*/
	function install( $p_fromdir = null ) {
		global $database;

		if (!$this->preInstallCheck( $p_fromdir, 'module' )) {
			return false;
		}

		$xmlDoc 	= $this->xmlDoc();
		$mosinstall =& $xmlDoc->documentElement;

		$client = '';
		if ($mosinstall->getAttribute( 'client' )) {
			$validClients = array( 'administrator' );
			if (!in_array( $mosinstall->getAttribute( 'client' ), $validClients )) {
				$this->setError( 1, JText::_( 'Unknown client type' ) .' ['.$mosinstall->getAttribute( 'client' ).']' );
				return false;
			}
			$client = 'admin';
		}

		// Set some vars
		$e = &$mosinstall->getElementsByPath( 'name', 1 );
		$this->elementName($e->getText());
		$this->elementDir( mosPathName( JPATH_SITE
			. ($client == 'admin' ? DS.'administrator' : '')
			. DS.'modules'.DS )
		);

		if ($this->parseFiles( 'files', 'module', JText::_( 'No file is marked as module file' ) ) === false) {
			return false;
		}
		$this->parseFiles( 'images' );

		$client_id = intval( $client == 'admin' );
		// Insert in module in DB
		$query = "SELECT id FROM #__modules"
		. "\n WHERE module = '". $this->elementSpecial() ."'"
		. "\n AND client_id = $client_id"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			$this->setError( 1, JText::_( 'SQL error' ) .': ' . $database->stderr( true ) );
			return false;
		}

		$id = $database->loadResult();

		if (!$id) {
			$row = new mosModule( $database );
			$row->title 		= $this->elementName();
			$row->ordering 		= 99;
			$row->position 		= 'left';
			$row->showtitle 	= 1;
			$row->iscore 		= 0;
			$row->access 		= $client == 'admin' ? 99 : 0;
			$row->client_id 	= $client_id;
			$row->module 		= $this->elementSpecial();

			$row->store();

			$query = "INSERT INTO #__modules_menu"
			. "\n VALUES ( $row->id, 0 )"
			;
			$database->setQuery( $query );
			if(!$database->query()) {
				$this->setError( 1, JText::_( 'SQL error' ) .': '. $database->stderr( true ) );
				return false;
			}
		} else {
			$this->setError( 1, JText::_( 'Module' ) .' "'. $this->elementName() .'" '. JText::_( 'already exists!' ) );
			return false;
		}
		if ($e = &$mosinstall->getElementsByPath( 'description', 1 )) {
			$this->setError( 0, $this->elementName() .'<p>'. $e->getText() .'</p>' );
		}

		// Add new positions
		$template_positions = &$mosinstall->getElementsByPath('install/positions', 1);
		if (!is_null($template_positions)) {
			$positions = $template_positions->childNodes;
			foreach($positions as $position)
			{
				$this->createTemplatePosition($position);
			}
		}

                // Are there any SQL queries??
		$query_element = &$mosinstall->getElementsByPath('install/queries', 1);
		if (!is_null($query_element)) {
			$queries = $query_element->childNodes;
			foreach($queries as $query)
			{
				$database->setQuery( $query->getText());
				if (!$database->query())
				{
					$this->setError( 1, $_LANG->_( 'SQL Error' ) ." " . $database->stderr( true ) );
					return false;
				}
			}
		}

		return $this->copySetupFile('front');
	}
	/**
	* Custom install method
	* @param int The id of the module
	* @param string The URL option
	* @param int The client id
	*/
	function uninstall( $id, $option, $client=0 ) {
		global $database;

		$id = intval( $id );

		$query = "SELECT module, iscore, client_id"
		. "\n FROM #__modules WHERE id = $id"
		;
		$database->setQuery( $query );
		$row = null;
		$database->loadObject( $row );

		if ($row->iscore) {
			HTML_installer::showInstallMessage( sprintf( JText::_( 'WARNCOREMODULE' ), $row->title ) .'<br />'. JText::_( 'WARNCORECOMPONENT2' ), JText::_( 'Uninstall - error' ), $this->returnTo( $option, 'module', $row->client_id ? '' : 'admin' ) );
			exit();
		}

		$query = "SELECT id"
		. "\n FROM #__modules"
		. "\n WHERE module = '". $row->module ."' AND client_id = '". $row->client_id ."'"
		;
		$database->setQuery( $query );
		$modules = $database->loadResultArray();

		if (count( $modules )) {
            $modID = implode( ',', $modules );

			$query = "DELETE FROM #__modules_menu"
			. "\n WHERE moduleid IN ('". $modID ."')"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				$msg = $database->stderr;
				die( $msg );
			}

    		$query = "DELETE FROM #__modules"
    		. "\n WHERE module = '". $row->module ."' AND client_id = '". $row->client_id ."'"
    		;
    		$database->setQuery( $query );
    		if (!$database->query()) {
    			$msg = $database->stderr;
    			die( $msg );
    		}

    		if ( !$row->client_id ) {
    			$basepath = JPATH_SITE . DS .'modules'. DS;
    		} else {
    			$basepath = JPATH_ADMINISTRATOR . DS .'modules'. DS;
    		}

      		$xmlfile = $basepath . $row->module . '.xml';

    			// see if there is an xml install file, must be same name as element
    		if (file_exists( $xmlfile )) {
    			$this->i_xmldoc =& JFactory::getXMLParser();
    			$this->i_xmldoc->resolveErrors( true );

    			if ($this->i_xmldoc->loadXML( $xmlfile, false, true )) {
    				$mosinstall =& $this->i_xmldoc->documentElement;
    				// get the files element
    				$files_element =& $mosinstall->getElementsByPath( 'files', 1 );
    				if (!is_null( $files_element )) {
    					$files = $files_element->childNodes;
    					foreach ($files as $file) {
    						// delete the files
    						$filename = $file->getText();
    						if (file_exists( $basepath . $filename )) {
    							$parts = pathinfo( $filename );
    							$subpath = $parts['dirname'];
    							if ($subpath <> '' && $subpath <> '.' && $subpath <> '..') {
    								echo '<br />'. JText::_( 'Deleting' ) .': '. $basepath . $subpath;
    								$result = deldir(mosPathName( $basepath . $subpath . DS ));
    							} else {
    								echo '<br />'. JText::_( 'Deleting' ) .': '. $basepath . $filename;
    								$result = unlink( mosPathName ($basepath . $filename, false));
    							}
    							echo intval( $result );
    						}
    					}

    					// remove XML file from front
    					echo JText::_( 'Deleting XML File' ) .": ". $xmlfile;
    					@unlink(  mosPathName ($xmlfile, false ) );
    					return true;
    				}
    			}
    		}
		}

	}
}
?>
