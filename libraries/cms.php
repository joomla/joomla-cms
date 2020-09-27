<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

trigger_error(
	sprintf(
		'Bootstrapping Joomla using the %1$s file is deprecated.  Use %2$s instead.',
		__FILE__,
		__DIR__ . '/bootstrap.php'
	),
	E_USER_DEPRECATED
);

require __DIR__ . '/bootstrap.php';
