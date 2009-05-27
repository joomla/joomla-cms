<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'add'  :
		TOOLBAR_contact::_EDIT(false);
		break;
	case 'edit' :
	case 'editA':
		TOOLBAR_contact::_EDIT(true);
		break;

	default:
		TOOLBAR_contact::_DEFAULT();
		break;
}