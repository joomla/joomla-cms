<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Only super user can view this data
if (!Factory::getUser()->authorise('core.admin'))
{
	return;
}

// Include dependencies.
JLoader::register('ModLatestActionsHelper', __DIR__ . '/helper.php');

$list = ModLatestActionsHelper::getList($params);

if ($params->get('automatic_title', 0))
{
	$module->title = ModLatestActionsHelper::getTitle($params);
}

require ModuleHelper::getLayoutPath('mod_latestactions', $params->get('layout', 'default'));
