<?php
/**
 * @version		$Id: toolbar.messages.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

switch ($task) {

	case 'view':
		TOOLBAR_messages::_VIEW();
		break;

	case 'add'  :
	case 'edit' :
	case 'reply':
		TOOLBAR_messages::_EDIT();
		break;

	case 'config':
		TOOLBAR_messages::_CONFIG();
		break;

	default:
		TOOLBAR_messages::_DEFAULT();
		break;
}