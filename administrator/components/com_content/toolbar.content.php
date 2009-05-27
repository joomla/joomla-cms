<?php
/**
 * @version		$Id: toolbar.content.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'add':
	case 'new_content_typed':
	case 'new_content_section':
		TOOLBAR_content::_EDIT(false);
		break;
	case 'edit':
	case 'editA':
	case 'edit_content_typed':
		TOOLBAR_content::_EDIT(true);
		break;
/*
	case 'showarchive':
		TOOLBAR_content::_ARCHIVE();
		break;
 */
	case 'movesect':
		TOOLBAR_content::_MOVE();
		break;

	case 'copy':
		TOOLBAR_content::_COPY();
		break;

	default:
		TOOLBAR_content::_DEFAULT();
		break;
}