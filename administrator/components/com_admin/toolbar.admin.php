<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'sysinfo':
		TOOLBAR_admin::_SYSINFO();
		break;

	case 'help':
		TOOLBAR_admin::_HELP();
		break;

	case 'preview':
	case 'preview2':
		TOOLBAR_admin::_PREVIEW();
		break;

	default:
		if ($task) {
			TOOLBAR_admin::_DEFAULT();
		} else {
			TOOLBAR_admin::_CPANEL();
		}
		break;
}