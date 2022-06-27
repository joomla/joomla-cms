<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default textfilter settings
 */



/**
 * Notifies users the changes from the default textfilter.
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   3.9.19
 */
function admin_postinstall_textfilter3919_condition()
{
    return true;
}
