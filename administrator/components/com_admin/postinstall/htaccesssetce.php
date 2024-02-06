<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default .htaccess file regarding setting the Content-Encoding header.
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Notifies users of a change in the default .htaccess file regarding setting the Content-Encoding header
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   4.2.9
 */
function admin_postinstall_htaccesssetce_condition()
{
    return true;
}
