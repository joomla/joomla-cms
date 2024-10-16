<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default .htaccess file regarding Brotli compression.
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Notifies users of a change in the default .htaccess file regarding setting for brotli to prevent double compression
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   4.4.4
 */
function admin_postinstall_htaccessbrotli_condition()
{
    $htaccessContent = '';

    if (is_file(JPATH_ROOT . '/.htaccess') || is_file(JPATH_ROOT . '/htaccess.txt')) {
        $htaccessContent = file_get_contents(is_file(JPATH_ROOT . '/.htaccess') ? JPATH_ROOT . '/.htaccess' : JPATH_ROOT . '/htaccess.txt');
    }

    return !str_contains($htaccessContent, 'E=no-brotli:1');
}
