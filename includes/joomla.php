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

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
define( '_MOS_MAMBO_INCLUDED', 1 );

/**
 * Intelligent file importer
 * @param string A dot syntax path
 * @param boolean True to use require_once, false to use require
 */
function jimport( $path, $requireOnce=true ) {
	global $mosConfig_absolute_path;

	$path = $mosConfig_absolute_path
		. DIRECTORY_SEPARATOR . str_replace( '.', DIRECTORY_SEPARATOR, $path );
	
	// TODO: Rules, try a few variations, search for factories, etc

	$path = $path . '.php';

	if ($requireOnce) {
		return require_once( $path );
	} else {
		return require( $path );
	}
}

if (phpversion() < '4.2.0') {
	jimport('libraries.joomla.compat.php41x' );
}
if (phpversion() < '4.3.0') {
	jimport('libraries.joomla.compat.php42x' );
}
if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('libraries.joomla.compat.php50x' );
}

@set_magic_quotes_runtime( 0 );

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

// experimenting

jimport( 'libraries.joomla.database.mysql' );

require_once( $mosConfig_absolute_path . '/includes/phpmailer/class.phpmailer.php' );
require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );

jimport( 'libraries.joomla.classes.object' );
jimport( 'libraries.joomla.version' );
jimport( 'libraries.joomla.functions' );
jimport( 'libraries.joomla.classes' );
jimport( 'libraries.joomla.models' );
jimport( 'libraries.joomla.html' );
jimport( 'libraries.joomla.factory' );
jimport( 'libraries.joomla.files' );
jimport( 'libraries.joomla.xml' );


/** @global $database */
$database =& JFactory::getDatabase();

/** @global $acl */
$acl =& JFactory::getACL();

/** @global $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();

/** @global $_VERSION */
$_VERSION = new JVersion();

//TODO : implement mambothandler class as singleton, add getBotHandler to JFactory

//TODO : implement editor functionality as a class
jimport( 'libraries.joomla.editor' );


//TODO : implement mambothandler class as singleton, add getVersion to JFactory
jimport( 'libraries.joomla.legacy' );
?>
