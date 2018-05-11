<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Logged\Administrator\Helper\LoggedHelper;

if ($params->get('automatic_title', 0))
{
	$module->title = LoggedHelper::getTitle($params);
}

// Check if session metadata tracking is enabled
if (Factory::getConfig()->get('session_metadata', true))
{
	$users = LoggedHelper::getList($params);

	require ModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
}
else
{
	require ModuleHelper::getLayoutPath('mod_logged', 'disabled');
}
