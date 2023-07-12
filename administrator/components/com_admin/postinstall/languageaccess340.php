<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checks if the installation is
 * affected by the issue with content languages access in 3.4.0
 */

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks if the installation is affected by the issue with content languages access in 3.4.0
 *
 * @link    https://github.com/joomla/joomla-cms/pull/6172
 * @link    https://github.com/joomla/joomla-cms/pull/6194
 *
 * @return  boolean
 *
 * @since   3.4.1
 */
function admin_postinstall_languageaccess340_condition()
{
    $db    = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select($db->quoteName('access'))
        ->from($db->quoteName('#__languages'))
        ->where($db->quoteName('access') . ' = ' . $db->quote('0'));
    $db->setQuery($query);
    $db->execute();
    $numRows = $db->getNumRows();

    if (isset($numRows) && $numRows != 0) {
        // We have rows here so we have at minimum one row with access set to 0
        return true;
    }

    // All good the query return nothing.
    return false;
}
