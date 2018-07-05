<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$buttons = \Joomla\Module\Quickicon\Administrator\Helper\QuickIconHelper::getButtons($params);

require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_quickicon', $params->get('layout', 'default'));
