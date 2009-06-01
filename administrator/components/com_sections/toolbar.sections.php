<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Sections
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task)
{
	case 'add'  :
		TOOLBAR_sections::_EDIT(false);
		break;
	case 'edit' :
	case 'editA':
		TOOLBAR_sections::_EDIT(true);
		break;

	case 'copyselect':
	case 'copysave':
		TOOLBAR_sections::_COPY();
		break;

	default:
		TOOLBAR_sections::_DEFAULT();
		break;
}