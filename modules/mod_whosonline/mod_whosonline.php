<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the whosonline functions only once
require_once __DIR__ . '/helper.php';

$showmode = $params->get('showmode', 0);

if ($showmode == 0 || $showmode == 2)
{
	$count = ModWhosonlineHelper::getOnlineCount();
}

if ($showmode > 0)
{
	$names = ModWhosonlineHelper::getOnlineUserNames($params);
}

$linknames = $params->get('linknames', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_whosonline', $params->get('layout', 'default'));
