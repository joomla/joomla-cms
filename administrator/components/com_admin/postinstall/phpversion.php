<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checking minimum PHP version support
 */

defined('_JEXEC') or die;

/**
 * Checks if the PHP version is less than 5.3.10.
 *
 * @return  integer
 *
 * @since   3.2
 */
function admin_postinstall_phpversion_condition()
{
	return version_compare(PHP_VERSION, '5.3.10', 'lt');
}
