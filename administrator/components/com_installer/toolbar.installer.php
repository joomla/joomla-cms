<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
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

require_once( $mainframe->getPath( 'toolbar_html' ) );

switch ($task){
	case 'new':
		TOOLBAR_installer::_NEW();
		break;

	default:
		$element = mosGetParam( $_REQUEST, 'element', '' );
		if ($element == 'component' || $element == 'module' || $element == 'mambot') {
			TOOLBAR_installer::_DEFAULT2();
		} else {
			TOOLBAR_installer::_DEFAULT();
		}
		break;
}
?>