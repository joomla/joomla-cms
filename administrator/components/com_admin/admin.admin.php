<?php
/**
* @version $Id: admin.admin.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

switch ($task) {

	case 'redirect':
		$goto = trim( strtolower( mosGetParam( $_REQUEST, 'link' ) ) );
		if ($goto == 'null') {
			$msg = $_LANG->_( 'WARNASSLINKITEM' );
			mosRedirect( 'index2.php?option=com_admin&task=listcomponents', $msg );
			exit();
		}
		$goto = str_replace( "'", '', $goto );
		mosRedirect($goto);
		break;

	case 'sysinfo':
		HTML_admin_misc::system_info();
		break;

	case 'help':
		HTML_admin_misc::help();
		break;

	case 'cancel':
		mosRedirect( 'index2.php' );
		break;

	case 'changelog';
		HTML_admin_misc::changelog();
		break;

	case 'cpanel':
	default:
		HTML_admin_misc::controlPanel();
		break;
}
?>