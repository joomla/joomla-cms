<?php
/**
* @version $Id: installation.php 1547 2005-12-23 08:43:51Z eddieajau $
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

error_reporting( E_ALL );
@set_magic_quotes_runtime( 0 );

if (file_exists( JPATH_CONFIGURATION . DS . 'configuration.php')) {
	if (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) > 10) {
		header( 'Location: ../index.php' );
		exit();
	}
}

//Globals
$GLOBALS['mosConfig_absolute_path'] = JPATH_SITE . DIRECTORY_SEPARATOR;
$GLOBALS['mosConfig_sitename']      = 'Joomla! - Web Installer';

require_once( JPATH_LIBRARIES . DS .'loader.php' );

if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}

//File includes
define( 'JPATH_INCLUDES', dirname(__FILE__) );

require_once( JPATH_INCLUDES . DS . 'functions.php' );
require_once( JPATH_INCLUDES . DS . 'classes.php' );
require_once( JPATH_INCLUDES . DS . 'html.php' );

//Library imports
jimport( 'joomla.common.base.object' );
jimport( 'joomla.common.compat.compat' );
jimport( 'joomla.version' );
jimport( 'joomla.utilities.error');
jimport( 'joomla.factory' );
jimport( 'joomla.filesystem.*' );
jimport( 'joomla.presentation.parameter.parameter' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.model.model' );
jimport( 'joomla.application.application');
jimport( 'joomla.application.environment.request' );
jimport( 'joomla.application.environment.session' );
jimport( 'joomla.application.user.user' );

// JString should only be loaded after pre-install checks
$task = mosGetParam( $_REQUEST, 'task', '' );
if (!($task == '' || $task == 'preinstall' || $task == 'lang')){
	jimport( 'joomla.i18n.string' );
}
?>