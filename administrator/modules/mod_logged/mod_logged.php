<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies.
require_once __DIR__ . '/helper.php';

$users = ModLoggedHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
