<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLog::add(
	'ConfigModelApplication has moved from ' . __DIR__ . '/application.php to ' . dirname(__DIR__) . '/model/application.',
	JLog::WARNING,
	'deprecated'
);

include_once JPATH_ADMINISTRATOR . '/components/com_config/model/application.php';
