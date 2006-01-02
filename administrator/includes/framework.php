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

@set_magic_quotes_runtime( 0 );

if (!file_exists( JPATH_SITE . DS .'configuration.php' )) {
	header( 'Location: ../installation/index.php' );
	exit();
}

//TODO : Fix offline message
//Installation sub folder check, removed for work with SVN
//if (file_exists( JPATH_INSTALLATION . DS .'index.php' )) {
//	define( '_INSTALL_CHECK', 1 );
//	include (JPATH_SITE . DS .'offline.php');
//	exit();
//}

//File includes
require_once( JPATH_SITE      		. DS .'globals.php' );
require_once( JPATH_CONFIGURATION   . DS .'configuration.php' );
require_once( JPATH_LIBRARIES 		. DS .'loader.php' );

define( 'JURL_SITE', $mosConfig_live_site );

if (phpversion() < '4.2.0') {
	jimport('joomla.common.compat.php41x' );
}
if (phpversion() < '4.3.0') {
	jimport('joomla.common.compat.php42x' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('joomla.common.compat.php50x' );
}

if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}

//Third party library imports
jimport( 'phpinputfilter.inputfilter' );

//Joomla library imports
jimport( 'joomla.common.compat.compat' );

jimport( 'joomla.version' );
jimport( 'joomla.functions' );
jimport( 'joomla.error');
jimport( 'joomla.auth');
jimport( 'joomla.profiler' );
jimport( 'joomla.application.environment.session' );
jimport( 'joomla.registry.main');
jimport( 'joomla.models.*' );
jimport( 'joomla.html' );
jimport( 'joomla.factory' );
jimport( 'joomla.files' );
jimport( 'joomla.params' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.i18n.string' );
jimport( 'joomla.event' );
jimport( 'joomla.plugin' );
jimport( 'joomla.editor' );
jimport( 'joomla.application.application');

jimport( 'joomla.common.legacy.*' );

?>