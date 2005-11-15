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

@set_magic_quotes_runtime( 0 );

// checks for configuration file, if none found loads installation page
if (!file_exists( JPATH_SITE . '/configuration.php' ) || filesize( JPATH_SITE .'/configuration.php' ) < 10) {
	$self = str_replace( '/index.php','', strtolower($_SERVER['PHP_SELF']) ). '/';
	header("Location: http://" . $_SERVER['HTTP_HOST'] . $self . "installation/index.php" );
	exit();
}

//Installation sub folder check, removed for work with CVS
/*if (file_exists( 'installation/index.php' )) {
	define( '_INSTALL_CHECK', 1 );
	include ('offline.php');
	exit();
}*/

//File includes
require_once( JPATH_SITE      . '/globals.php' );
require_once( JPATH_SITE      . '/configuration.php' );
require_once( JPATH_LIBRARIES . '/loader.php' );

if (phpversion() < '4.2.0') {
	jimport('joomla.compat.php41x' );
}
if (phpversion() < '4.3.0') {
	jimport('joomla.compat.php42x' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('joomla.compat.php50x' );
}

if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

// experimenting

jimport( 'phpmailer.phpmailer');
jimport( 'phpinputfilter.inputfilter' );
jimport( 'joomla.database.mysql' );
jimport( 'joomla.version' );
jimport( 'joomla.functions' );
jimport( 'joomla.classes' );
jimport( 'joomla.classes.app');
jimport( 'joomla.classes.profiler');
jimport( 'joomla.models.*' );
jimport( 'joomla.html' );
jimport( 'joomla.factory' );
jimport( 'joomla.files' );
jimport( 'joomla.xml' );
jimport( 'joomla.language' );

/**
* Joomla! Mainframe class
*
* Provide many supporting API functions
* @package Joomla
*/
class JSite extends JApplication {
	
	/**
	* Class constructor
	* @param database A database connection object
	*/
	function __construct() {
		$database =& JFactory::getDBO();
		parent::__construct($database, 0);
	}
}


/** @global $database */
$database =& JFactory::getDBO();

/** @global $acl */
$acl =& JFactory::getACL();

/** @global $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();

/** @global $_VERSION */
$_VERSION = new JVersion();

//TODO : implement mambothandler class as singleton, add getBotHandler to JFactory

//TODO : implement editor functionality as a class
jimport( 'joomla.editor' );


//TODO : implement mambothandler class as singleton, add getVersion to JFactory
jimport( 'joomla.legacy' );
?>
