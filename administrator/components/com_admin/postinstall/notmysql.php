<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checking for non-MySQL database engines
 */

defined('_JEXEC') or die;

/**
 * Checks if the database engine is not MySQL
 *
 * @return  boolean
 *
 * @since   3.5
 */
function admin_postinstall_notmysql_condition()
{
	return strpos(JFactory::getConfig()->get('dbtype'), 'mysql') === false;
}
