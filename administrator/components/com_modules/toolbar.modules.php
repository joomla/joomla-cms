<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JApplicationHelper::getPath('toolbar_html'));

$client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

switch ($task) {

	case 'editA':
	case 'edit':
		TOOLBAR_modules::_EDIT($client);
		break;

	case 'add':
		TOOLBAR_modules::_NEW($client);
		break;

	default:
		TOOLBAR_modules::_DEFAULT($client);
		break;
}