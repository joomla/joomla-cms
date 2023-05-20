<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default .htaccess and web.config files.
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Notifies users of the add the nosniff headers by applying the changes from the default .htaccess or web.config file
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   3.4
 */
function admin_postinstall_addnosniff_condition()
{
    return true;
}
