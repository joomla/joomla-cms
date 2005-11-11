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

define( '_VALID_MOS', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( 'includes/defines.php');
require_once( 'includes/installation.php' );

header( 'Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
header( 'Pragma: no-cache');	// HTTP/1.0
header(' Content-Type: text/html; charset=UTF-8');

$vars = mosGetParam( $_POST, 'vars', array() );
$mosConfig_lang = mosGetParam( $vars, 'lang', detectLanguage() );

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

	case 'makedb':
		if (installationTasks::makeDB()) {
			installationTasks::mainConfig( 1 );
		}
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