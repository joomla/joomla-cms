<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Whosonline\Site\Helper\WhosonlineHelper;

$showmode = $params->get('showmode', 0);

if ($showmode == 0 || $showmode == 2)
{
	$count = WhosonlineHelper::getOnlineCount();
}

if ($showmode > 0)
{
	$names = WhosonlineHelper::getOnlineUserNames($params);
}

require ModuleHelper::getLayoutPath('mod_whosonline', $params->get('layout', 'default'));
