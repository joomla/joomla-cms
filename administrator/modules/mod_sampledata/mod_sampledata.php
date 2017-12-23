<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependencies.
JLoader::register('ModSampledataHelper', __DIR__ . '/helper.php');

$items = ModSampledataHelper::getList();
require JModuleHelper::getLayoutPath('mod_sampledata', $params->get('layout', 'default'));
