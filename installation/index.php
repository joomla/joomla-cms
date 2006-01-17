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

require_once( JPATH_BASE .'/includes/defines.php'     );
require_once( JPATH_BASE .'/includes/application.php' );
require_once( JPATH_BASE .'/includes/template.php'    );

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

$document =& $mainframe->getDocument();
$document->parse( 'template', 'index.html', JPATH_BASE);

//initialise the document
initDocument($document);

$task = mosGetParam( $_REQUEST, 'task', '' );

$result = '';

switch ($task)
{
	case 'preinstall':
		$result = installationTasks::preInstall();
		break;

	case 'license':
		$result = installationTasks::license();
		break;

	case 'dbconfig':
		$result = installationTasks::dbConfig();
		break;

	case 'dbcollation':
		$result = installationTasks::dbCollation();
		break;

	case 'makedb':
		if (installationTasks::makeDB()) {
			$result = installationTasks::ftpConfig( 1 );
		}
		break;

	case 'ftpconfig':
		$result = installationTasks::ftpConfig();
		break;

	case 'mainconfig':
		$result = installationTasks::mainConfig();
		break;

	case 'saveconfig':
		$buffer = installationTasks::saveConfig();
		$result = installationTasks::finish( $buffer );
		break;

	case 'lang':
	default:
		$result = installationTasks::chooseLanguage();
		break;
}

$document->addGlobalVar('installation_', $result);

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );		// HTTP/1.1
header( 'Pragma: no-cache' );										// HTTP/1.0


$document->display( 'index.html', true);
?>