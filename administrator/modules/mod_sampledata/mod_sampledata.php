<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$items = \Joomla\Module\Sampledata\Administrator\Helper\SampledataHelper::getList();

require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_sampledata', $params->get('layout', 'default'));
