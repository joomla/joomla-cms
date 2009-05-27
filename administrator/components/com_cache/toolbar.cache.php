<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
		case 'purgeadmin':
		TOOLBAR_cache::_PURGEADMIN();
		break;
		case 'purge':
		TOOLBAR_cache::_PURGEADMIN();
		break;
		default:
		TOOLBAR_cache::_DEFAULT();
		break;

}