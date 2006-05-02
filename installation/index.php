<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname( __FILE__ ) );

require_once( JPATH_BASE .'/includes/defines.php'     );
require_once( JPATH_BASE .'/includes/application.php' );

// create the mainframe object
$mainframe = new JInstallation();

// create the session
$mainframe->setSession('installation');

$registry =& JSession::get('registry');
$registry->loadArray(JRequest::getVar( 'vars', array(), 'post', 'array' ), 'application');

$configLang = $mainframe->getUserState('application.lang');

//set language
$mainframe->setLanguage($configLang);

// load the language
$lang =& $mainframe->getLanguage();
$lang->_load( JPATH_BASE . '/language/' . $configLang . '/' . $configLang .'.ini' );

$task = JRequest::getVar( 'task' );

$vars = $registry->toArray('application');

$result = '';

switch ($task)
{
	case 'preinstall':
		$result = JInstallationController::preInstall($vars);
		break;

	case 'license':
		$result = JInstallationController::license($vars);
		break;

	case 'dbconfig':
		$result = JInstallationController::dbConfig($vars);
		break;

	case 'dbcollation':
		$result = JInstallationController::dbCollation($vars);
		break;

	case 'makedb':
		if (JInstallationController::makeDB($vars)) {
			$result = JInstallationController::ftpConfig( $vars, 1 );
		}
		break;

	case 'ftpconfig':
		$result = JInstallationController::ftpConfig($vars);
		break;

	case 'mainconfig':
		$result = JInstallationController::mainConfig($vars);
		break;

	case 'saveconfig':
		$buffer = JInstallationController::saveConfig($vars);
		$result = JInstallationController::finish( $vars, $buffer );
		break;

	case 'lang':
	default:
		$result = JInstallationController::chooseLanguage($vars);
		break;
}

$document =& $mainframe->getDocument();
$document->setRenderer('installation', '' , $result);
$document->setTitle( 'Joomla! - Web Installer' );
$document->display( 'template', 'index.html', false, array('directory' => JPATH_BASE));
?>