<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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