<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$tabclass_arr = array ('sectiontableentry2', 'sectiontableentry1');

$menu 	= &JSite::getMenu();
$items	= $menu->getItems('link', 'index.php?option=com_poll&view=poll');
$itemid = isset($items[0]) ? '&Itemid='.$items[0]->id : '';

$poll   = modPollHelper::getPoll($params->get( 'id', 0 ));

if ( $poll && $poll->id ) {
	$layout = JModuleHelper::getLayoutPath('mod_poll');
	$tabcnt = 0;
	$options = modPollHelper::getPollOptions($poll->id);

	require($layout);
}