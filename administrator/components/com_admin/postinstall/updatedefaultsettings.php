<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in various default settings.
 */

defined('_JEXEC') or die;

/**
 * Notifies users of a change in various default settings
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   3.8.8
 */
function admin_postinstall_updatedefaultsettings_condition()
{
	return true;
}
