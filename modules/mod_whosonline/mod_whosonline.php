<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the whosonline functions only once
require_once dirname(__FILE__).DS.'helper.php';

$showmode = $params->get( 'showmode', 0 );

if ($showmode == 0 || $showmode == 2) {
	$count 	= modWhosonlineHelper::getOnlineCount();
}

if ($showmode > 0) {
	$names 	= modWhosonlineHelper::getOnlineMemberNames();
}

require(JModuleHelper::getLayoutPath('mod_whosonline'));
