<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default .htaccess file regarding preventing execution of scripts in uploaded file
 * directories.
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Notifies users of a change in the default .htaccess file regarding preventing execution
 * of scripts in uploaded file directories.
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   __DEPLOY_VERSION__
 */
function admin_postinstall_htaccessmedia_condition()
{
    return true;
}
