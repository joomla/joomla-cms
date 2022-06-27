<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checking minimum PHP version support
 */



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
