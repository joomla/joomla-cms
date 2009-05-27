<?php
/**
 * @version		$Id: toolbar.newsfeeds.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'add' :
		TOOLBAR_newsfeeds::_EDIT(false);
		break;
	case 'edit':
		TOOLBAR_newsfeeds::_EDIT(true);
		break;

	default:
		TOOLBAR_newsfeeds::_DEFAULT();
		break;
}