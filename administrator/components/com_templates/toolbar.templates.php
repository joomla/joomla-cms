<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Templates
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

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

$client = mosGetParam( $_REQUEST, 'client', 'site' );

switch ($task) {

	case 'view':
		TOOLBAR_templates::_VIEW($client);
		break;

	case 'edit_source':
		TOOLBAR_templates::_EDIT_SOURCE($client);
		break;

	case 'edit_params':
		TOOLBAR_templates::_EDIT_PARAMS($client);
		break;

	case 'choose_css':
		TOOLBAR_templates::_CHOOSE_CSS($client);
		break;

	case 'edit_css':
		TOOLBAR_templates::_EDIT_CSS($client);
		break;

	case 'assign':
		TOOLBAR_templates::_ASSIGN($client);
		break;

	case 'positions':
		TOOLBAR_templates::_POSITIONS();
		break;

	default:
		TOOLBAR_templates::_DEFAULT($client);
		break;
}
?>