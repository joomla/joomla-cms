<?php
/**
 * @version		$Id: admin.admin.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
require_once JApplicationHelper::getPath('admin_html');

switch ($task)
{
	case 'sysinfo':
		HTML_admin_misc::system_info();
		break;

	case 'changelog':
		HTML_admin_misc::changelog();
		break;

	case 'help':
		HTML_admin_misc::help();
		break;

	case 'version':
		HTML_admin_misc::version();
		break;

	case 'preview':
		HTML_admin_misc::preview();
		break;

	case 'preview2':
		HTML_admin_misc::preview(1);
		break;

	case 'keepalive':
		return;
		break;
}