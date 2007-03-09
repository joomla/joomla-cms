<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$tabclass_arr = array ('sectiontableentry2', 'sectiontableentry1');

$menu 	= &JMenu::getInstance();
$items	= $menu->getItems('link', 'index.php?option=com_poll');
$itemid = isset($items[0]) ? $items[0]->id : '0';

$list = modPollHelper::getList($params);
$layout = JModuleHelper::getLayoutPath('mod_poll');

foreach ($list as $item)
{
	$tabcnt 	= 0;
	$voted 	= JRequest::getVar( "voted$item->id", 'z', 'COOKIE' );

	if ($item->id && $item->title)  {
		$options = modPollHelper::getPollOptions($item->id);
	}

	require($layout);
}