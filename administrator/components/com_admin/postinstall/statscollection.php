<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checking minimum PHP version support
 */

defined('_JEXEC') or die;

/**
 * Alerts the user we are collecting anonymous data as of Joomla 3.5.0.
 *
 * @return  boolean
 *
 * @since   3.5
 */
function admin_postinstall_statscollection_condition()
{
	return true;
}
