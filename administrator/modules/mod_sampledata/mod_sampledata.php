<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependencies.
require_once __DIR__ . '/helper.php';

$items = ModSampledataHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_sampledata', $params->get('layout', 'default'));
