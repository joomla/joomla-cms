<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname( __FILE__ ) );

require_once( JPATH_BASE .'/includes/defines.php' );
require_once( JPATH_BASE .'/includes/application.php' );

// create the mainframe object
$mainframe =& new JInstallation();

// create the session
$mainframe->setSession('installation');

// get the vars array from the request and add it to the session
$vars = (array) mosGetParam( $_POST, 'vars' );
$mainframe->setUserState('application.vars', $vars);

// get the language from the request and add it to the session
$configLang = mosGetParam( $vars, 'lang', 'eng_GB' );
$mainframe->setUserState('application.lang', $configLang);

// load the language
$lang =& $mainframe->getLanguage();
$lang->_load( JPATH_BASE . '/language/' . $configLang . '/' . $configLang .'.ini' );

header( 'Cache-Control: no-cache, must-revalidate' );	// HTTP/1.1
header( 'Pragma: no-cache' );							// HTTP/1.0
header(' Content-Type: text/html; charset=UTF-8' );

$task = mosGetParam( $_REQUEST, 'task', '' );

switch ($task) {
	case 'preinstall':
		installationTasks::preInstall();
		break;

	case 'license':
		installationTasks::license();
		break;

	case 'dbconfig':
		installationTasks::dbConfig();
		break;

	case 'dbcollation':
		installationTasks::dbCollation();
		break;

	case 'makedb':
		if (installationTasks::makeDB()) {
			installationTasks::ftpConfig( 1 );
		}
		break;

	case 'ftpconfig':
		installationTasks::ftpConfig();
		break;

	case 'mainconfig':
		installationTasks::mainConfig();
		break;

	case 'saveconfig':
		$buffer = installationTasks::saveConfig();
		installationTasks::finish( $buffer );
		break;

	case 'lang':
	default:
		installationTasks::chooseLanguage();
		break;
}
?>
