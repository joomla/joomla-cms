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
defined( '_JEXEC' ) or die( 'Restricted access' );
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

define( 'JURL_SITE', $mosConfig_live_site );

if (phpversion() < '4.2.0') {
	jimport('joomla.compat.php41x' );
}
if (phpversion() < '4.3.0') {
	jimport('joomla.compat.php42x' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('joomla.compat.php50x' );
}

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

jimport('joomla.compat.phputf8env');

//Library imports
jimport( 'phpmailer.phpmailer');
jimport( 'phpinputfilter.inputfilter' );

jimport( 'joomla.version' );
jimport( 'joomla.functions' );
jimport( 'joomla.system.error');
jimport( 'joomla.system.app');
jimport( 'joomla.system.auth');
jimport( 'joomla.system.profiler');
jimport( 'joomla.system.session' );
jimport( 'joomla.system.string' );
jimport( 'joomla.models.*' );
jimport( 'joomla.html' );
jimport( 'joomla.factory' );
jimport( 'joomla.files' );
jimport( 'joomla.params' );
jimport( 'joomla.language' );
jimport( 'joomla.event' );
jimport( 'joomla.plugin' );
jimport( 'joomla.editor' );

/**
* Joomla! Mainframe class
*
* Provide many supporting API functions
* @package Joomla
*/
class JSite extends JApplication {

	/**
	* Class constructor
	* @param integer A client id
	*/
	function __construct($option) {
		parent::__construct($option, 0);
	}
}

/** @global $_VERSION */
$_VERSION = new JVersion();

jimport( 'joomla.legacy' );
?>