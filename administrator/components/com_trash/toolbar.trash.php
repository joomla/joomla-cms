<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Trash
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JApplicationHelper::getPath('toolbar_html');

switch ($task)
{
	case 'restoreconfirm':
		TOOLBAR_Trash::_RESTORE();
		break;

	case 'deleteconfirm':
		TOOLBAR_Trash::_DELETE();
		break;

	default:
		TOOLBAR_Trash::_DEFAULT();
		break;
}