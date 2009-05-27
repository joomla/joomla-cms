<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'add'  :
		TOOLBAR_categories::_EDIT(false);
		break;

	case 'edit' :
	case 'editA':
		TOOLBAR_categories::_EDIT(true);
		break;

	case 'moveselect':
	case 'movesave':
		TOOLBAR_categories::_MOVE();
		break;

	case 'copyselect':
	case 'copysave':
		TOOLBAR_categories::_COPY();
		break;

	default:
		TOOLBAR_categories::_DEFAULT();
		break;
}