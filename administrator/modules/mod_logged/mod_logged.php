<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Logged\Administrator\Helper\LoggedHelper;

$users = LoggedHelper::getList($params);

if ($params->get('automatic_title', 0))
{
	$module->title = LoggedHelper::getTitle($params);
}

require ModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
