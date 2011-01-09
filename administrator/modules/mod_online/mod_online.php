<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the mod_online functions only once.
require_once dirname(__FILE__).'/helper.php';

// Get layout data.
$count = modOnlineHelper::getOnlineCount();

if ($count !== false) {
	// Render the module.
	require JModuleHelper::getLayoutPath('mod_online', $params->get('layout', 'default'));
}