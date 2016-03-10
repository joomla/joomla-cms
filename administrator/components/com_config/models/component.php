<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLog::add(
	'ConfigModelComponent has moved from ' . __FILE__ . ' to ' . dirname(__DIR__) . '/model/component.php.',
	JLog::WARNING,
	'deprecated'
);

include_once JPATH_ADMINISTRATOR . '/components/com_config/model/component.php';
