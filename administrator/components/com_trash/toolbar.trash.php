<?php
/**
 * @version		$Id: toolbar.trash.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Trash
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

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