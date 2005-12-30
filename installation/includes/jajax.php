<?php
/**
 * @version $Id: $
 * @package Joomla
 * @subpackage Installation
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname( __FILE__ ) );

//Global definitions
define( 'DS', DIRECTORY_SEPARATOR );

//Joomla framework path definitions
$parts = explode( DS, JPATH_BASE );
array_pop( $parts );
array_pop( $parts );

define( 'JPATH_ROOT',			implode( DS, $parts ) );
define( 'JPATH_SITE',			JPATH_ROOT );
define( 'JPATH_CONFIGURATION',	JPATH_ROOT );
define( 'JPATH_LIBRARIES',		JPATH_ROOT . DS . 'libraries' );

// Require the library loader
require_once( JPATH_LIBRARIES . DS .'loader.php' );

jimport( 'joomla.system.string' );

/*
 * Check to see if the form was sent via the ajform library
 */
if (isset ($_GET['ajform']) && (bool) $_GET['ajform']) {

	switch ($_GET['task']) {
		case 'ftproot':
			JAJAXHandler::ftproot();
			break;
		case 'dbcollate':
			JAJAXHandler::dbcollate();
			break;
		default:
			JAJAXHandler::fail();
			break;
	}
} else {
	// Do nothing
}

/**
 * AJAX Task handler class
 * 
 * @static
 * @package Joomla
 * @subpackage Installer
 * @since 1.1
 */
class JAJAXHandler {
	
	/**
	 * Method to get the path from the FTP root to the Joomla root directory
	 */
	function ftproot() {
		jimport( 'joomla.system.error' );
		require_once(JPATH_BASE.DS."classes.php");
		echo JInstallationHelper::findFtpRoot($_GET['user'], $_GET['pass']);
	}

	/**
	 * Method to get the database collations
	 */
	function dbcollate() {
		
		/*
		 * Get a database connection instance
		 */		
		jimport( 'joomla.database.database' );
		$database = & JDatabase :: getInstance($_GET['type'], $_GET['host'], $_GET['user'], $_GET['pass'] );

		if ($err = $database->getErrorNum()) {
			if ($err != 3) {
				// connection failed
				echo "Connection Failed";
			}
		}
		/*
		 * This needs to be rewritten for output to a javascript method... 
		 */
		$collations = array();

		// determine db version, utf support and available collations
		$vars['DBversion'] = $database->getVersion();
		$verParts = explode( '.', $vars['DBversion'] );
		$vars['DButfSupport'] = ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int) $verParts[2] >= 2));
		if ($vars['DButfSupport']) {
			$query = "SHOW COLLATION LIKE 'utf8%'";
			$database->setQuery( $query );
			$collations = $database->loadAssocList();
			// Tell javascript we have UTF support
			echo "true\n";
		} else {
			// backward compatibility - utf-8 data in non-utf database
			// collation does not really have effect so default charset and collation is set
			$collations[0]['Collation'] = 'latin1';
			// Tell javascript we do not have UTF support
			echo "false\n";
		}
		$txt = '<select id="vars_dbcollation" name="vars[DBcollation]" class="inputbox" size="1">';
		
		foreach ($collations as $collation) {
			$txt .= '<option>'.$collation["Collation"].'</option>';
		}
		$txt .=	'</select>';
		
		echo $txt;
	}

	function fail() {
		echo "Invalid AJAX Task";
	}
}
?>