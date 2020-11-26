<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_user
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$user = $app->getIdentity();

require ModuleHelper::getLayoutPath('mod_user', $params->get('layout', 'default'));
