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

/**
 * Post system checks
 */


@set_magic_quotes_runtime( 0 );

if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10)) {
	header( 'Location: installation/index.php' );
	exit();
}

if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}

/**
 * System startup
 */

//System includes
require_once( JPATH_SITE      	  . DS . 'globals.php' );
require_once( JPATH_CONFIGURATION . DS . 'configuration.php' );
require_once( JPATH_LIBRARIES 	  . DS . 'loader.php' );

//System configuration
$CONFIG = new JConfig();

if (@$CONFIG->error_reporting === 0) {
	error_reporting( 0 );
} else if (@$CONFIG->error_reporting > 0) {
	error_reporting( $CONFIG->error_reporting );
}

define('JDEBUG', $CONFIG->debug); 

unset($CONFIG);

//System profiler
if(JDEBUG) {
	jimport('joomla.utilities.profiler');
	$_PROFILER =& JProfiler::getInstance('Application');
}

/**
 * Framework loading
 */

//Joomla library imports
jimport( 'joomla.common.compat.compat' );

jimport( 'joomla.version' );
jimport( 'joomla.utilities.functions' );
jimport( 'joomla.utilities.error');
jimport( 'joomla.application.user.user' );
jimport( 'joomla.application.environment.session' );
jimport( 'joomla.application.environment.request' );
jimport( 'joomla.model.model' );
jimport( 'joomla.presentation.html' );
jimport( 'joomla.factory' );
jimport( 'joomla.filesystem.*' );
jimport( 'joomla.presentation.parameter.parameter' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.i18n.string' );
jimport( 'joomla.application.event' );
jimport( 'joomla.application.extension.plugin' );
jimport( 'joomla.application.application');
jimport( 'joomla.application.menu' );

// support for legacy classes & functions that will be depreciated
jimport( 'joomla.common.legacy.*' );

JDEBUG ? $_PROFILER->mark('afterLoadFramework') : null;
?>