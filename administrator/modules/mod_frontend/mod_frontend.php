<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_frontend
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$sitename = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_frontend', $params->get('layout', 'default'));
